# System Status Monitor

A real-time system monitoring plugin for Pelican Panel. Display CPU, memory, disk usage, and server uptime.

## Features

- **CPU** - Usage percentage with model and cores
- **Memory** - RAM and Swap with progress bars
- **Disk** - Space used, total, and free
- **Load Average** - System load (1, 5, 15 min)
- **Uptime** - Server uptime and last reboot
- **System Info** - OS, PHP, Hostname, Processes
- **Nodes** - CPU, RAM, Swap, Disk, Load and online status of every node (via Wings)

## What's Included

- Admin dashboard page with full metrics
- Quick widget summary (CPU, Memory, Disk) for the panel **and** every node
- Auto-refresh every 10 seconds
- Docker aware: reads `/proc` and cgroup limits instead of parsing `free`
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

- Linux server (including Docker)
- PHP 8.1+
- No external commands are executed: metrics are read from `/proc` and cgroups
- Node stats require the `viewList node` permission (root admins always have it)

## Troubleshooting

**"Unable to load system information"**
- Verify it's a Linux server
- Make sure `/proc` is readable by the PHP user

---

Need help? Join the [Pelican Discord](https://discord.gg/pelican-panel)