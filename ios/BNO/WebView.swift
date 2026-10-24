import SwiftUI
import UIKit
import WebKit

/// Состояние встроенного браузера для SwiftUI.
@MainActor
final class BrowserModel: ObservableObject {
    @Published var canGoBack = false
    @Published var canGoForward = false
    @Published var isLoading = false
    @Published var progress: Double = 0
    @Published var errorMessage: String?

    weak var webView: WKWebView?

    func goBack() { webView?.goBack() }
    func goForward() { webView?.goForward() }
    func stop() { webView?.stopLoading() }

    func load(_ url: URL) {
        errorMessage = nil
        webView?.load(URLRequest(url: url))
    }

    /// Обновить страницу; если ни одна страница ещё не открылась — загрузить `fallback`.
    func reload(fallback: URL?) {
        errorMessage = nil
        if webView?.url != nil {
            webView?.reload()
        } else if let fallback {
            load(fallback)
        }
    }

    func clearWebsiteData(completion: @escaping () -> Void) {
        let store = WKWebsiteDataStore.default()
        Task {
            await store.removeData(ofTypes: WKWebsiteDataStore.allWebsiteDataTypes(), modifiedSince: .distantPast)
            completion()
        }
    }
}

struct WebView: UIViewRepresentable {
    @ObservedObject var model: BrowserModel
    let baseURL: URL

    func makeCoordinator() -> Coordinator {
        Coordinator(model: model, baseURL: baseURL)
    }

    func makeUIView(context: Context) -> WKWebView {
        let config = WKWebViewConfiguration()
        // По этой метке сайт понимает, что открыт в приложении.
        config.applicationNameForUserAgent = "BNOApp/\(Bundle.main.shortVersion)"
        config.allowsInlineMediaPlayback = true
        config.websiteDataStore = .default()

        let web = WKWebView(frame: .zero, configuration: config)
        web.isOpaque = false
        web.backgroundColor = Theme.uiBg
        web.scrollView.backgroundColor = Theme.uiBg
        web.allowsBackForwardNavigationGestures = true
        // Отступы под «чёлку» сайт делает сам (viewport-fit=cover + env(safe-area-inset-*)).
        web.scrollView.contentInsetAdjustmentBehavior = .never
        web.navigationDelegate = context.coordinator
        web.uiDelegate = context.coordinator

        let refresh = UIRefreshControl()
        refresh.tintColor = Theme.uiActive
        refresh.addTarget(context.coordinator, action: #selector(Coordinator.pullToRefresh(_:)), for: .valueChanged)
        web.scrollView.refreshControl = refresh

        context.coordinator.observe(web)
        model.webView = web
        web.load(URLRequest(url: baseURL))
        return web
    }

    func updateUIView(_ uiView: WKWebView, context: Context) {}

    @MainActor
    final class Coordinator: NSObject, WKNavigationDelegate, WKUIDelegate {
        private let model: BrowserModel
        private let baseURL: URL
        private var observations: [NSKeyValueObservation] = []

        init(model: BrowserModel, baseURL: URL) {
            self.model = model
            self.baseURL = baseURL
        }

        func observe(_ web: WKWebView) {
            // Значения берём из change.newValue и передаём в модель на главном акторе.
            let model = self.model
            observations = [
                web.observe(\.estimatedProgress, options: [.new]) { [weak model] _, change in
                    guard let model else { return }
                    let value = change.newValue ?? 0
                    Task { @MainActor in model.progress = value }
                },
                web.observe(\.isLoading, options: [.new]) { [weak model] _, change in
                    guard let model else { return }
                    let value = change.newValue ?? false
                    Task { @MainActor in model.isLoading = value }
                },
                web.observe(\.canGoBack, options: [.new]) { [weak model] _, change in
                    guard let model else { return }
                    let value = change.newValue ?? false
                    Task { @MainActor in model.canGoBack = value }
                },
                web.observe(\.canGoForward, options: [.new]) { [weak model] _, change in
                    guard let model else { return }
                    let value = change.newValue ?? false
                    Task { @MainActor in model.canGoForward = value }
                },
            ]
        }

        @objc func pullToRefresh(_ sender: UIRefreshControl) {
            model.reload(fallback: baseURL)
            Task {
                try? await Task.sleep(nanoseconds: 600_000_000)
                sender.endRefreshing()
            }
        }

        private func isOwnSite(_ url: URL) -> Bool {
            url.host == baseURL.host && url.port == baseURL.port
        }

        // MARK: - Навигация

        // Async-варианты делегатов: их сигнатура совпадает с SDK независимо от пометок @MainActor/@Sendable.
        func webView(_ webView: WKWebView, decidePolicyFor navigationAction: WKNavigationAction) async -> WKNavigationActionPolicy {
            guard let url = navigationAction.request.url, let scheme = url.scheme?.lowercased() else {
                return .allow
            }

            // Почта, телефон и прочие схемы — системными приложениями.
            if !["http", "https", "about", "blob", "data"].contains(scheme) {
                _ = await UIApplication.shared.open(url)
                return .cancel
            }

            // Ссылки на чужие сайты открываем в Safari, чтобы не уходить из архива.
            if navigationAction.navigationType == .linkActivated, ["http", "https"].contains(scheme), !isOwnSite(url) {
                _ = await UIApplication.shared.open(url)
                return .cancel
            }

            return .allow
        }

        func webView(_ webView: WKWebView, didStartProvisionalNavigation navigation: WKNavigation!) {
            model.errorMessage = nil
        }

        func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
            model.errorMessage = nil
            webView.scrollView.refreshControl?.endRefreshing()
        }

        func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
            handle(error, in: webView)
        }

        func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
            handle(error, in: webView)
        }

        func webViewWebContentProcessDidTerminate(_ webView: WKWebView) {
            webView.reload()
        }

        private func handle(_ error: Error, in webView: WKWebView) {
            webView.scrollView.refreshControl?.endRefreshing()
            let nsError = error as NSError
            // Отменённая загрузка (переход по другой ссылке) — не ошибка.
            if nsError.domain == NSURLErrorDomain && nsError.code == NSURLErrorCancelled {
                return
            }
            // «Frame load interrupted» — WebKit отдал файл системе, тоже не ошибка.
            if nsError.domain == "WebKitErrorDomain" && nsError.code == 102 {
                return
            }
            model.errorMessage = nsError.localizedDescription
        }

        // MARK: - Окна и диалоги JavaScript

        /// target="_blank": свои страницы открываем здесь же, чужие — в Safari.
        func webView(_ webView: WKWebView, createWebViewWith configuration: WKWebViewConfiguration,
                     for navigationAction: WKNavigationAction, windowFeatures: WKWindowFeatures) -> WKWebView? {
            if navigationAction.targetFrame == nil, let url = navigationAction.request.url {
                if isOwnSite(url) {
                    webView.load(navigationAction.request)
                } else {
                    UIApplication.shared.open(url)
                }
            }
            return nil
        }

        func webView(_ webView: WKWebView, runJavaScriptAlertPanelWithMessage message: String,
                     initiatedByFrame frame: WKFrameInfo) async {
            let alert = UIAlertController(title: nil, message: message, preferredStyle: .alert)
            _ = await present(alert, actions: [("ОК", .default, true)], fallback: true)
        }

        func webView(_ webView: WKWebView, runJavaScriptConfirmPanelWithMessage message: String,
                     initiatedByFrame frame: WKFrameInfo) async -> Bool {
            let alert = UIAlertController(title: nil, message: message, preferredStyle: .alert)
            return await present(alert, actions: [("Отмена", .cancel, false), ("ОК", .default, true)], fallback: false)
        }

        func webView(_ webView: WKWebView, runJavaScriptTextInputPanelWithPrompt prompt: String, defaultText: String?,
                     initiatedByFrame frame: WKFrameInfo) async -> String? {
            let alert = UIAlertController(title: nil, message: prompt, preferredStyle: .alert)
            alert.addTextField { field in
                field.text = defaultText
                field.clearButtonMode = .whileEditing
            }
            let confirmed = await present(alert, actions: [("Отмена", .cancel, false), ("ОК", .default, true)], fallback: false)
            return confirmed ? (alert.textFields?.first?.text ?? "") : nil
        }

        /// Показывает диалог и ждёт нажатия. Возвращает значение нажатой кнопки.
        private func present(_ alert: UIAlertController, actions: [(String, UIAlertAction.Style, Bool)], fallback: Bool) async -> Bool {
            guard let top = UIApplication.shared.topViewController else {
                return fallback
            }
            return await withCheckedContinuation { continuation in
                for (title, style, value) in actions {
                    alert.addAction(UIAlertAction(title: title, style: style) { _ in continuation.resume(returning: value) })
                }
                alert.view.tintColor = Theme.uiActive
                top.present(alert, animated: true)
            }
        }
    }
}

extension UIApplication {
    /// Самый верхний показанный контроллер — чтобы показывать диалоги поверх листов настроек.
    var topViewController: UIViewController? {
        let scenes = connectedScenes.compactMap { $0 as? UIWindowScene }
        let scene = scenes.first { $0.activationState == .foregroundActive } ?? scenes.first
        var top = scene?.windows.first { $0.isKeyWindow }?.rootViewController
        while let presented = top?.presentedViewController {
            top = presented
        }
        return top
    }
}
