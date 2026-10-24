import SwiftUI

/// Экран подключения: адрес сервера, проверка связи, недавние адреса.
/// Показывается при первом запуске и в настройках.
struct ConnectView: View {
    @EnvironmentObject private var settings: AppSettings
    @Environment(\.dismiss) private var dismiss

    var isInitial: Bool
    var onClearData: (() -> Void)? = nil

    @State private var address = ""
    @State private var status: CheckStatus = .idle
    @State private var confirmClear = false
    @FocusState private var fieldFocused: Bool

    enum CheckStatus: Equatable {
        case idle
        case checking
        case ok(String)
        case failed(String)
    }

    var body: some View {
        ZStack {
            Theme.bg.ignoresSafeArea()

            ScrollView {
                VStack(alignment: .leading, spacing: 26) {
                    header
                    addressSection
                    if !settings.recentServers.isEmpty {
                        recentSection
                    }
                    actions
                    if !isInitial {
                        dataSection
                    }
                    footer
                }
                .padding(.horizontal, 20)
                .padding(.vertical, 28)
                .frame(maxWidth: 520)
                .frame(maxWidth: .infinity)
            }
            .scrollDismissesKeyboard(.interactively)

            Scanlines().opacity(0.6)
        }
        .onAppear {
            address = settings.serverURL?.absoluteString ?? ""
            if isInitial && address.isEmpty {
                fieldFocused = true
            }
        }
        .confirmationDialog("Выйти из аккаунта и очистить данные сайта?", isPresented: $confirmClear, titleVisibility: .visible) {
            Button("Очистить", role: .destructive) {
                onClearData?()
                dismiss()
            }
            Button("Отмена", role: .cancel) {}
        }
    }

    private var header: some View {
        HStack(alignment: .center, spacing: 14) {
            LogoMark(size: 56)
            VStack(alignment: .leading, spacing: 4) {
                Text(isInitial ? "ПОДКЛЮЧЕНИЕ" : "НАСТРОЙКИ")
                    .font(Theme.mono(22, .medium))
                    .tracking(4)
                    .foregroundColor(Theme.active)
                Text("Терминал доступа к архиву Бюро")
                    .font(Theme.mono(11))
                    .foregroundColor(Theme.muted)
            }
            Spacer()
            if !isInitial {
                Button {
                    dismiss()
                } label: {
                    Image(systemName: "xmark")
                        .font(.system(size: 15, weight: .medium))
                        .foregroundColor(Theme.text)
                        .frame(width: 40, height: 40)
                        .overlay(Rectangle().stroke(Theme.border, lineWidth: 1))
                }
                .accessibilityLabel("Закрыть")
            }
        }
    }

    private var addressSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            FieldLabel(text: "Адрес сервера")
            TextField("", text: $address, prompt: Text("archive.example.ru или 192.168.1.10:8000").foregroundColor(Theme.muted.opacity(0.7)))
                .font(Theme.mono(16))
                .foregroundColor(Theme.text)
                .keyboardType(.URL)
                .textContentType(.URL)
                .textInputAutocapitalization(.never)
                .autocorrectionDisabled()
                .submitLabel(.go)
                .focused($fieldFocused)
                .onSubmit(connect)
                .onChange(of: address) { _ in status = .idle }
                .padding(.horizontal, 12)
                .padding(.vertical, 12)
                .background(Color.black)
                .overlay(Rectangle().stroke(fieldFocused ? Theme.active : Theme.border, lineWidth: 1))

            statusLine
        }
    }

    @ViewBuilder
    private var statusLine: some View {
        switch status {
        case .idle:
            Text("Без http:// — для домена будет https, для IP-адреса — http.")
                .font(Theme.mono(11))
                .foregroundColor(Theme.muted)
        case .checking:
            HStack(spacing: 8) {
                ProgressView().tint(Theme.active).scaleEffect(0.8)
                Text("Проверка связи…").font(Theme.mono(12)).foregroundColor(Theme.muted)
            }
        case .ok(let message):
            Label(message, systemImage: "checkmark.circle")
                .font(Theme.mono(12))
                .foregroundColor(Theme.active)
        case .failed(let message):
            Label(message, systemImage: "exclamationmark.triangle")
                .font(Theme.mono(12))
                .foregroundColor(Theme.alarm)
        }
    }

    private var recentSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            FieldLabel(text: "Недавние")
            VStack(spacing: 0) {
                ForEach(settings.recentServers, id: \.self) { item in
                    HStack {
                        Button {
                            address = item
                        } label: {
                            HStack {
                                Image(systemName: item == settings.serverURL?.absoluteString ? "dot.radiowaves.left.and.right" : "clock")
                                    .foregroundColor(Theme.muted)
                                Text(item)
                                    .font(Theme.mono(13))
                                    .foregroundColor(Theme.text)
                                    .lineLimit(1)
                                    .truncationMode(.middle)
                                Spacer()
                            }
                            .contentShape(Rectangle())
                        }
                        Button {
                            settings.forgetRecent(item)
                        } label: {
                            Image(systemName: "xmark").font(.system(size: 11)).foregroundColor(Theme.muted)
                        }
                        .accessibilityLabel("Забыть адрес")
                    }
                    .padding(.horizontal, 12)
                    .padding(.vertical, 11)
                    .overlay(alignment: .bottom) { Rectangle().fill(Theme.border.opacity(0.6)).frame(height: 1) }
                }
            }
            .background(Theme.panel)
            .overlay(Rectangle().stroke(Theme.border, lineWidth: 1))
        }
    }

    private var actions: some View {
        HStack(spacing: 10) {
            Button("Проверить", action: { Task { await check() } })
                .buttonStyle(TerminalButtonStyle())
            Button("Подключиться", action: connect)
                .buttonStyle(TerminalButtonStyle(primary: true))
        }
        .disabled(status == .checking || address.trimmingCharacters(in: .whitespaces).isEmpty)
    }

    private var dataSection: some View {
        VStack(alignment: .leading, spacing: 8) {
            FieldLabel(text: "Данные")
            Button("Выйти и очистить данные сайта") { confirmClear = true }
                .buttonStyle(TerminalButtonStyle(danger: true))
            Text("Удалит cookie и кеш: вы выйдете из админки на этом устройстве.")
                .font(Theme.mono(11))
                .foregroundColor(Theme.muted)
        }
    }

    private var footer: some View {
        Text("БНО · версия \(Bundle.main.shortVersion)")
            .font(Theme.mono(10))
            .tracking(1)
            .foregroundColor(Theme.muted.opacity(0.7))
            .frame(maxWidth: .infinity)
            .padding(.top, 10)
    }

    // MARK: - Действия

    private func connect() {
        guard let url = AppSettings.normalize(address) else {
            status = .failed("Некорректный адрес")
            return
        }
        settings.serverURL = url
        if !isInitial {
            dismiss()
        }
    }

    /// Запрос к /up — служебному адресу Laravel, который отвечает 200, если сайт жив.
    private func check() async {
        guard let url = AppSettings.normalize(address) else {
            status = .failed("Некорректный адрес")
            return
        }
        status = .checking
        var request = URLRequest(url: url.appendingPathComponent("up"))
        request.timeoutInterval = 8
        request.cachePolicy = .reloadIgnoringLocalCacheData

        do {
            let (_, response) = try await URLSession.shared.data(for: request)
            let code = (response as? HTTPURLResponse)?.statusCode ?? 0
            status = (200..<400).contains(code)
                ? .ok("Архив на связи (\(url.host ?? ""))")
                : .failed("Сервер ответил кодом \(code)")
        } catch {
            status = .failed(error.localizedDescription)
        }
    }
}
