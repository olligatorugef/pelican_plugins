# 🖥️ System Status Monitor

Un plugin Pelican pour surveiller et afficher les informations système en temps réel, incluant l'utilisation CPU, mémoire, disque et le temps de fonctionnement du serveur.

**Disponible en 🇬🇧 English et 🇫🇷 Français**

## 🎯 Caractéristiques

### Affichages Détaillés

- **💻 CPU Usage** - Pourcentage d'utilisation du CPU avec modèle et nombre de cœurs
- **🧠 Memory Usage** - RAM et mémoire virtuelle avec graphiques de progression
- **💾 Disk Usage** - Espace disque avec seuils de couleur et espace libre
- **📈 Load Average** - Charge système (1, 5, 15 minutes)
- **⏱️ System Uptime** - Temps de fonctionnement et dernier redémarrage
- **🖥️ System Info** - Système d'exploitation, PHP, hostname, nombre de processus

### Interface Enrichie

- **🎨 Design coloré** - Codes couleur visuels (vert/orange/rouge)
- **📊 Graphiques** - Barres de progression animées avec pourcentages
- **📱 Responsive** - Compatible mobile et desktop
- **🌐 Multilingue** - Support FR et EN

## 📊 Données Disponibles

```json
{
    "cpu": 0.71,                              // Utilisation CPU (0-100%)
    "cpu_cores": 14,                          // Nombre de cœurs
    "cpu_model": "Intel(R) Core(TM) i7",      // Modèle du processeur
    "memory": {
        "used": "6.05 GB",                    // RAM utilisée
        "used_raw": 6493552640,               // En bytes
        "total": "28.68 GB",                  // RAM totale
        "total_raw": 30793728000,             // En bytes
        "percent": 21.09                      // Pourcentage (0-100%)
    },
    "swap": {
        "used": "0 B",                        // Mémoire virtuelle utilisée
        "total": "4 GB",                      // Mémoire virtuelle totale
        "percent": 0                          // Pourcentage (0-100%)
    },
    "disk": {
        "used": "357.06 GB",                  // Disque utilisé
        "total": "980.73 GB",                 // Disque total
        "free": "623.67 GB",                  // Disque libre
        "percent": 36.41                      // Pourcentage (0-100%)
    },
    "load": {
        "1min": 0.42,                         // Charge 1 minute
        "5min": 0.24,                         // Charge 5 minutes
        "15min": 0.21                         // Charge 15 minutes
    },
    "uptime": "up 4 days, 21 hours, 24 minutes",
    "last_reboot": "2026-01-08 23:52:25",     // Dernier redémarrage
    "processes": {
        "total": 277,                         // Nombre de processus
        "running": 277
    },
    "hostname": "pterodactyl",                // Nom du serveur
    "os": "Linux",                            // Système d'exploitation
    "php_version": "8.3.6"                    // Version PHP
}
```

## 📦 Installation

Le plugin est maintenant disponible dans ce dépôt. Pour l'installer :

```bash
# Cloner le répertoire
git clone https://github.com/olligatorugef/pelican_plugins.git
cd pelican_plugins/system-status-monitor

# Installer le plugin dans Pelican
# Suivez les instructions de votre installation Pelican
```

## 🚀 Utilisation

### Widget du Tableau de Bord

Le plugin ajoute un widget coloré au tableau de bord de l'admin avec :
- Vue rapide des stats principales
- Codes couleur visuels
- Mise à jour en temps réel

### Page d'Administration

Une page dédiée **"System Status"** dans le menu de navigation avec :
- Tous les détails du système
- Interface complète et élégante
- Informations détaillées par section

## 🎨 Interface

### Seuils de Couleur

Les graphiques utilisent des codes couleur pour une meilleure lisibilité :

| Couleur | État | Utilisation |
|---------|------|------------|
| 🟢 Vert | Excellent | < 60% |
| 🟠 Orange | Attention | 60-80% |
| 🔴 Rouge | Critique | > 80% |

### Sections Principales

1. **Section CPU** - Modèle, cœurs, utilisation, charge moyenne
2. **Section Mémoire** - RAM et mémoire virtuelle avec détails
3. **Section Disque** - Espace utilisé, total et libre
4. **Section Système** - OS, PHP, hostname, processus, redémarrage

## 🌍 Localisation

Le plugin supporte automatiquement :
- **Français (FR)** - Interface complète en français
- **Anglais (EN)** - Interface complète en anglais

La langue s'ajuste selon la locale de l'application.

## 📁 Structure du Plugin

```
system-status-monitor/
├── src/
│   ├── Services/
│   │   └── SystemInfoService.php           (Récupération données)
│   ├── Filament/admin/
│   │   ├── Pages/SystemStatus.php          (Page admin détaillée)
│   │   └── Widgets/SystemStatusWidget.php  (Widget dashboard)
│   ├── Providers/
│   └── SystemStatusMonitorPlugin.php
├── resources/views/
│   ├── pages/system-status.blade.php       (Vue page enrichie)
│   └── widgets/system-status.blade.php     (Vue widget amélioré)
├── lang/
│   ├── en/messages.php                     (Traductions EN)
│   └── fr-FR/messages.php                  (Traductions FR)
├── plugin.json
├── README.md
└── INSTALLATION.md
```

## ✨ Fonctionnalités Techniques

### Service SystemInfoService

Gère l'extraction des informations système avec support pour :
- **Linux** : /proc/stat, free, uptime, ps
- **Windows** : WMI (Windows Management Instrumentation)
- **Gestion d'erreurs** : Retourne des valeurs par défaut si données indisponibles
- **Performance** : Calculs optimisés sans blocage

### Méthodologie

- Format binaire pour les calculs (bytes, kilobytes, etc.)
- Conversion lisible automatique (B, KB, MB, GB, TB)
- Gestion des cas limites et erreurs
- Pas de dépendances externes

## 🔒 Compatibilité

- **Pelican Panel** : ^1.0.0-beta30
- **PHP** : 8.1+
- **OS** : Linux et Windows
- **Panels** : Admin panel uniquement

## 📝 Exemple d'Utilisation Avancée

### Ajouter le widget à une page personnalisée

```php
use Xolli\SystemStatusMonitor\Filament\admin\Widgets\SystemStatusWidget;

class CustomPage extends Page
{
    protected function getHeaderWidgets(): array
    {
        return [
            SystemStatusWidget::class,
        ];
    }
}
```

### Utiliser le service directement

```php
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

// Obtenir toutes les informations
$systemInfo = SystemInfoService::getSystemInfo();

// Ou des informations spécifiques
$cpu = SystemInfoService::getCpuUsage();
$memory = SystemInfoService::getMemoryUsage();
$disk = SystemInfoService::getDiskUsage();
$cpuCores = SystemInfoService::getCpuCores();
$cpuModel = SystemInfoService::getCpuModel();
$swap = SystemInfoService::getSwapMemory();
$processes = SystemInfoService::getProcessCount();
$hostname = SystemInfoService::getHostname();
$lastReboot = SystemInfoService::getLastReboot();
```

## 🎨 Personnalisation

Les vues utilisent Blade PHP et Tailwind CSS, faciles à personnaliser.

## 📄 Auteur

**Xolli**

## 📜 Licence

MIT License - Libre d'utilisation et de modification

---

## 🆘 Besoin d'Aide ?

Visitez le [Discord Pelican](https://discord.gg/pelican-panel) pour toute question ou suggestion !

### Prochaines Amélirations Possibles

- [ ] Notifications d'alerte
- [ ] Historique des données
- [ ] Graphiques temporels
- [ ] Export PDF/CSV
- [ ] Seuils configurables
- [ ] Intégration API
