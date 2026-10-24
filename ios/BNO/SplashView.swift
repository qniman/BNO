import SwiftUI

/// Заставка: знак и «печатающаяся» строка терминала.
struct SplashView: View {
    @State private var typed = ""
    @State private var cursorVisible = true

    private let line = "УСТАНОВКА СОЕДИНЕНИЯ С АРХИВОМ…"

    var body: some View {
        ZStack {
            Theme.bg.ignoresSafeArea()

            VStack(spacing: 22) {
                LogoMark(size: 96)

                VStack(spacing: 6) {
                    Text("БЮРО НЕТИПИЧНЫХ ОБЪЕКТОВ")
                        .font(Theme.mono(13, .medium))
                        .tracking(3)
                        .foregroundColor(Theme.active)

                    HStack(spacing: 0) {
                        Text(typed)
                        Text("█").opacity(cursorVisible ? 1 : 0)
                    }
                    .font(Theme.mono(11))
                    .foregroundColor(Theme.muted)
                }
            }

            Scanlines()
        }
        .task {
            for char in line {
                typed.append(char)
                try? await Task.sleep(nanoseconds: 28_000_000)
            }
        }
        .onAppear {
            withAnimation(.easeInOut(duration: 0.5).repeatForever()) { cursorVisible.toggle() }
        }
    }
}
