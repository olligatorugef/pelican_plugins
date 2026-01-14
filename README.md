# System Status Monitor

A real-time system monitoring plugin for Pelican Panel. Display CPU, memory, disk usage, and server uptime.

## Features

- 💻 **CPU** - Usage percentage with model and cores
- 🧠 **Memory** - RAM and Swap with progress bars
- 💾 **Disk** - Space used, total, and free
- 📈 **Load Average** - System load (1, 5, 15 min)
- ⏱️ **Uptime** - Server uptime and last reboot
- 🖥️ **System Info** - OS, PHP, Hostname, Processes

## What's Included

- Admin dashboard page with full metrics
- Quick widget summary (CPU, Memory, Disk)
- Color-coded status (Green / Yellow / Red)
- Responsive design
- English & French translations

## Installation

1. Extract to `plugins/system-status-monitor`
2. Go to Admin Panel → Plugins → Import
3. Enable the plugin
4. Access via Admin Panel → System Status

## Usage

The plugin provides:

- **Dashboard Widget** - Quick 3-line summary (CPU, Memory, Disk)
- **Admin Page** - Full system dashboard with detailed metrics

## Technical Details

- **Framework**: Filament PHP v4.x
- **Panel Version**: Pelican ^1.0.0-beta30
- **Author**: X_olli
- **License**: MIT

## Requirements

- Linux server (or Windows with WMI support)
- PHP 8.1+
- Commands: `nproc`, `free`, `uptime`, `ps`

## Troubleshooting

**"Unable to load system information"**
- Verify it's a Linux server
- Check required commands are available
- Ensure PHP has execute permissions

---

Need help? Join the [Pelican Discord](https://discord.gg/pelican-panel)
