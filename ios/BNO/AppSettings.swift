import Foundation

/// Адрес сервера архива и недавние адреса. Хранится в UserDefaults.
final class AppSettings: ObservableObject {
    @Published var serverURL: URL? {
        didSet { save() }
    }

    @Published private(set) var recentServers: [String]

    private let defaults = UserDefaults.standard
    private static let serverKey = "serverURL"
    private static let recentKey = "recentServers"

    init() {
        serverURL = UserDefaults.standard.string(forKey: Self.serverKey).flatMap(URL.init(string:))
        recentServers = UserDefaults.standard.stringArray(forKey: Self.recentKey) ?? []
    }

    func forgetRecent(_ address: String) {
        recentServers.removeAll { $0 == address }
        defaults.set(recentServers, forKey: Self.recentKey)
    }

    private func save() {
        guard let url = serverURL else {
            defaults.removeObject(forKey: Self.serverKey)
            return
        }
        let address = url.absoluteString
        defaults.set(address, forKey: Self.serverKey)
        recentServers = [address] + recentServers.filter { $0 != address }.prefix(4)
        defaults.set(recentServers, forKey: Self.recentKey)
    }

    /// «archive.ru», «192.168.1.5:8000», «https://archive.ru/» → полноценный URL.
    /// Для IP-адресов и localhost по умолчанию http, для доменов — https.
    static func normalize(_ input: String) -> URL? {
        var text = input.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !text.isEmpty else { return nil }

        let lower = text.lowercased()
        if !lower.hasPrefix("http://") && !lower.hasPrefix("https://") {
            let host = text.split(separator: "/").first.map(String.init) ?? text
            let isLocal = host.first?.isNumber == true || host.hasPrefix("localhost") || host.hasSuffix(".local")
            text = (isLocal ? "http://" : "https://") + text
        }
        while text.hasSuffix("/") {
            text.removeLast()
        }

        guard let url = URL(string: text), let host = url.host, !host.isEmpty else { return nil }
        return url
    }
}

extension Bundle {
    var shortVersion: String {
        infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0"
    }
}
