import SwiftUI

@main
struct BNOApp: App {
    @StateObject private var settings = AppSettings()

    var body: some Scene {
        WindowGroup {
            RootView()
                .environmentObject(settings)
                .preferredColorScheme(.dark)
                .tint(Theme.active)
        }
    }
}

/// Заставка → экран подключения (если адрес не задан) → архив.
struct RootView: View {
    @EnvironmentObject private var settings: AppSettings
    @State private var showSplash = true

    var body: some View {
        ZStack {
            Theme.bg.ignoresSafeArea()

            if let url = settings.serverURL {
                BrowserScreen(baseURL: url)
                    .id(url)
            } else {
                ConnectView(isInitial: true)
            }

            if showSplash {
                SplashView()
                    .transition(.opacity)
                    .zIndex(1)
            }
        }
        .task {
            try? await Task.sleep(nanoseconds: 1_300_000_000)
            withAnimation(.easeOut(duration: 0.4)) { showSplash = false }
        }
    }
}
