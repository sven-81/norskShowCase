# 📘 Norsk API – Architektur-Blueprint

## 🧭 Überblick

Dies ist ein mehrkontextuelles Sprachlern-System for Norwegisch mit Fokus auf nachhaltige Wortschatzverwaltung und Training. Es folgt Prinzipien von:

- **Domain-Driven Design (DDD)**
- **Hexagonal Architecture (Ports & Adapters)**
- **Clean Code & Clean Architecture**

---

## 📂 Projektstruktur

```plaintext
api/src/
├── manager/    → Verwaltung von Vokabeln
├── trainer/    → Trainingseinheiten & Lernfortschritt
├── user/       → Authentifizierung & Authorizierung
├── shared/     → Kontextfreie Bausteine (Value Objects, Interfaces)
├── infrastructure/ → Technische Infrastruktur (Logging, DB, Routing)
````

Jeder Bereich folgt dem klassischen DDD-Schichtenmodell:

```plaintext
├── domain/        → Domänenlogik, Modelle, Policies, Exceptions
├── application/   → Use Cases, Services, Eingangsport-Handler
├── infrastructure/→ Adapter (z.B. DB, HTTP, Filesystem)
├── web/           → Web-/REST-Controller & Response-Klassen
```

---

## 🔁 Hexagonal Architecture

Die Anwendung wird **von innen nach außen** gedacht:

* **Core (Domain + Application)** ist **technologieunabhängig**.
* **Ports** definieren Schnittstellen zu persistenter Speicherung, I/O, etc.
* **Adapters** implementieren diese Schnittstellen in `infrastructure`.
* ✅ Verwendung von CQRS (Command vs Query Use Cases)

### Beispiel:

```
ManagedWord (domain)
 ↳ verwendet VocabularyPersistencePort (interface)
 ↳ implementiert in ManagerWriter (infrastructure)
 ↳ gesteuert über WordUpdater (application)
 ↳ aufgerufen via WordManager (web/controller)
```

---

## 📦 Module im Detail

### 🧠 `manager/`

* **Zweck**: CRUD-Operationen für Vokabeln
* **Submodule**: `words`, `verbs`
* **Use Cases**: `CreateWord`, `UpdateVerb`, etc.
* **Repositories**: `ManagerWriter`, `WordReader`

---

### 🎯 `trainer/`

* **Zweck**: Auswahl & Bewertung von Vokabeln zum Trainieren
* **Mechanismen**: `RandomGenerator`, `SuccessCounter`
* **Use Cases**: `GetWordToTrain`, `SaveTrainedVerb`
* **Reader/Writer**: `TrainingWriter`, `VerbTrainingReader`

---

### 👤 `user/`

* **Zweck**: Authentifizierung, Registrierung, Rollenmanagement
* **Modelle**: `RegisteredUser`, `JwtAuthenticatedUser`
* **Ports**: `UserReadingRepository`, `UserWritingRepository`
* **Services**: `JwtService`, `AuthorizationStrategy`

---

### 🧩 `shared/`

* **Value Objects**: `Id`, `German`, `Norsk`
* **Polymorphe Interfaces**: `Vocabulary`, `ManagingVocabulary`
* **Technische Hilfen**: `Json`, `SanitizedClientInput`
* **Typen**: `VocabularyType`, `VocabularyPersistencePort`

---

## 🔌 Infrastruktur

### Routing

* Eigener HTTP-Router mit `ControllerResolver`, `CorsMiddleware`
* Kein Framework-Zwang – Custom HTTP-Layer

### Persistenz

* DB-Abstraktion über `MysqliWrapper`, `GenericSqlStatement`
* Klare Trennung nach Kontext (`Trainer`, `Manager`, `User`)
* Queries als explizite Klassen: `GetAllWordsForUserSql`, `AddUserSql`, etc.

### Logging

* Konfigurierbar über `AppLoggerConfig`
* Nutzung eigener ValueObjects (`LogMessage`)

---

## 📐 Clean Code-Prinzipien

* Keine Domain-Logik in Adaptern
* Alle Use Cases als eigenständige Klassen
* Value Objects statt primitiver Typen (`Norsk`, `German`, `Id`)
* Exception Handling über dedizierte Mappings (`TrainerExceptionMapper`, etc.)

---

## 🧪 Teststrategie

* Domain & Application Layer sind vollständig isoliert testbar
* Infrastruktur kann über Fake-Ports oder Mocks ersetzt werden
* Spezialfälle wie "ungültiger Typ" testbar über Dummy-Implementierungen
* UseCases via Behavior-Tests

---

## 🚧 Mögliche Weiterentwicklungen

* 🛠 API-Versionierung im Routing
* 🔐 Rate Limiting oder Rollenberechtigungen tiefer integriert
* 📊 Reporting/Statistiken als neuer Bounded Context

---

## 👥 Für neue Entwickler\:innen

> Einstiegspunkte:

| Bereich | Einstiegsklasse                     |
| ------- | ----------------------------------- |
| Manager | `WordManager`, `WordUpdater`        |
| Trainer | `GetWordToTrain`, `RandomGenerator` |
| Auth    | `LoginUser`, `JwtAuthenticatedUser` |
| Routing | `Router`, `ControllerResolver`      |

