import SwiftUI
import UIKit

/// Палитра и шрифты — те же, что на сайте.
enum Theme {
    static let bg = Color(red: 0.039, green: 0.039, blue: 0.039)
    static let panel = Color(red: 0.067, green: 0.067, blue: 0.067)
    static let card = Color(red: 0.106, green: 0.106, blue: 0.106)
    static let border = Color(red: 0.227, green: 0.227, blue: 0.227)
    static let muted = Color(red: 0.49, green: 0.52, blue: 0.49)
    static let text = Color(red: 0.81, green: 0.81, blue: 0.78)
    static let active = Color(red: 0.647, green: 0.722, blue: 0.604)
    static let alarm = Color(red: 0.69, green: 0.25, blue: 0.25)
    static let warn = Color(red: 0.79, green: 0.64, blue: 0.29)

    static let uiBg = UIColor(red: 0.039, green: 0.039, blue: 0.039, alpha: 1)
    static let uiActive = UIColor(red: 0.647, green: 0.722, blue: 0.604, alpha: 1)

    static func mono(_ size: CGFloat, _ weight: Font.Weight = .regular) -> Font {
        .system(size: size, weight: weight, design: .monospaced)
    }

    static func serif(_ size: CGFloat) -> Font {
        .system(size: size, weight: .regular, design: .serif)
    }
}

/// Кнопка в стиле терминала: рамка, моноширинный капс.
struct TerminalButtonStyle: ButtonStyle {
    var primary = false
    var danger = false

    func makeBody(configuration: Configuration) -> some View {
        let tint = danger ? Theme.alarm : (primary ? Theme.active : Theme.border)

        return configuration.label
            .font(Theme.mono(13, .medium))
            .textCase(.uppercase)
            .tracking(1.2)
            .padding(.horizontal, 14)
            .padding(.vertical, 12)
            .frame(maxWidth: .infinity)
            .foregroundColor(primary ? .black : (danger ? Theme.alarm : Theme.text))
            .background(primary ? Theme.active : Color.clear)
            .overlay(Rectangle().stroke(tint, lineWidth: 1))
            .contentShape(Rectangle())
            .opacity(configuration.isPressed ? 0.6 : 1)
    }
}

/// Квадратный знак «БНО».
struct LogoMark: View {
    var size: CGFloat = 64

    var body: some View {
        Text("БНО")
            .font(Theme.mono(size * 0.32, .semibold))
            .tracking(size * 0.03)
            .foregroundColor(Theme.active)
            .frame(width: size, height: size)
            .background(Color.black)
            .overlay(Rectangle().stroke(Theme.border, lineWidth: 1))
    }
}

/// Полосы развёртки ЭЛТ-монитора поверх нативных экранов.
struct Scanlines: View {
    var body: some View {
        Canvas { context, size in
            var y: CGFloat = 0
            while y < size.height {
                context.fill(Path(CGRect(x: 0, y: y, width: size.width, height: 1)), with: .color(.black.opacity(0.28)))
                y += 3
            }
        }
        .allowsHitTesting(false)
        .ignoresSafeArea()
    }
}

/// Подпись поля в стиле сайта.
struct FieldLabel: View {
    let text: String

    var body: some View {
        Text(text)
            .font(Theme.mono(11))
            .textCase(.uppercase)
            .tracking(1)
            .foregroundColor(Theme.muted)
    }
}
