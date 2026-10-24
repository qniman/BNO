import SwiftUI
import WebKit

/// Основной экран: сайт архива во встроенном браузере и нижняя панель навигации.
struct BrowserScreen: View {
    let baseURL: URL

    @EnvironmentObject private var settings: AppSettings
    @StateObject private var model = BrowserModel()
    @State private var showSettings = false

    var body: some View {
        VStack(spacing: 0) {
            ZStack(alignment: .top) {
                WebView(model: model, baseURL: baseURL)
                    .ignoresSafeArea(edges: .top)

                if model.isLoading {
                    ProgressLine(value: model.progress)
                }

                if let error = model.errorMessage {
                    OfflineView(
                        message: error,
                        host: baseURL.host ?? baseURL.absoluteString,
                        retry: { model.reload(fallback: baseURL) },
                        openSettings: { showSettings = true }
                    )
                    .transition(.opacity)
                }
            }

            BottomBar(
                model: model,
                home: { model.load(baseURL) },
                openSettings: { showSettings = true }
            )
        }
        .background(Theme.bg.ignoresSafeArea())
        .animation(.easeInOut(duration: 0.2), value: model.errorMessage)
        .sheet(isPresented: $showSettings) {
            ConnectView(isInitial: false, onClearData: {
                model.clearWebsiteData { model.load(baseURL) }
            })
            .environmentObject(settings)
        }
    }
}

/// Тонкая полоса загрузки под статус-баром.
private struct ProgressLine: View {
    let value: Double

    var body: some View {
        GeometryReader { geo in
            Rectangle()
                .fill(Theme.active)
                .frame(width: geo.size.width * max(0.05, value), height: 2)
                .shadow(color: Theme.active.opacity(0.8), radius: 3)
                .animation(.easeOut(duration: 0.2), value: value)
        }
        .frame(height: 2)
        .allowsHitTesting(false)
    }
}

/// Нижняя панель: назад, вперёд, главная, обновить, настройки.
private struct BottomBar: View {
    @ObservedObject var model: BrowserModel
    let home: () -> Void
    let openSettings: () -> Void

    var body: some View {
        HStack(spacing: 0) {
            barButton("chevron.left", label: "Назад", enabled: model.canGoBack) { model.goBack() }
            barButton("chevron.right", label: "Вперёд", enabled: model.canGoForward) { model.goForward() }
            barButton("square.grid.2x2", label: "Главная", enabled: true, action: home)
            barButton(model.isLoading ? "xmark" : "arrow.clockwise", label: model.isLoading ? "Остановить" : "Обновить", enabled: true) {
                model.isLoading ? model.stop() : model.reload(fallback: nil)
            }
            barButton("slider.horizontal.3", label: "Настройки", enabled: true, action: openSettings)
        }
        .padding(.horizontal, 6)
        .frame(height: 50)
        .background(Theme.panel.ignoresSafeArea(edges: .bottom))
        .overlay(alignment: .top) {
            Rectangle().fill(Theme.border).frame(height: 1)
        }
    }

    private func barButton(_ icon: String, label: String, enabled: Bool, action: @escaping () -> Void) -> some View {
        Button(action: action) {
            Image(systemName: icon)
                .font(.system(size: 17, weight: .regular))
                .frame(maxWidth: .infinity, maxHeight: .infinity)
                .contentShape(Rectangle())
        }
        .foregroundColor(enabled ? Theme.text : Theme.border)
        .disabled(!enabled)
        .accessibilityLabel(label)
    }
}

/// Экран «нет связи» в стиле архива.
private struct OfflineView: View {
    let message: String
    let host: String
    let retry: () -> Void
    let openSettings: () -> Void

    var body: some View {
        ZStack {
            Theme.bg.ignoresSafeArea()

            VStack(alignment: .leading, spacing: 18) {
                Text("СВЯЗЬ С АРХИВОМ ПОТЕРЯНА")
                    .font(Theme.mono(18, .medium))
                    .tracking(2)
                    .foregroundColor(Theme.alarm)

                VStack(alignment: .leading, spacing: 6) {
                    Text("Узел: \(host)")
                    Text("Ответ: \(message)")
                }
                .font(Theme.mono(12))
                .foregroundColor(Theme.muted)

                Text("Проверьте подключение к интернету и адрес сервера в настройках.")
                    .font(Theme.serif(16))
                    .foregroundColor(Theme.text)

                HStack(spacing: 10) {
                    Button("Настройки", action: openSettings).buttonStyle(TerminalButtonStyle())
                    Button("Повторить", action: retry).buttonStyle(TerminalButtonStyle(primary: true))
                }
            }
            .padding(24)
            .frame(maxWidth: 480)
            .overlay(Rectangle().stroke(Theme.alarm.opacity(0.5), lineWidth: 1))
            .padding(20)

            Scanlines().opacity(0.6)
        }
    }
}
