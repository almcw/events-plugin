# Events Plugin

Lightweight WordPress plugin for creating and displaying public events. Built as the foundation for the Saltburn events portal at happening.co.uk.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Upload the `events-plugin` folder to `/wp-content/plugins/`.
2. Activate in **Plugins > Installed Plugins**.
3. Visit **Settings > Permalinks** and click Save to flush rewrite rules (or the activation hook handles this automatically).

## Usage

### Events archive

Events are available at `/events/` automatically once the plugin is active.

### Event categories

Events can be assigned to one or more categories via the **Events > Categories** menu in wp-admin. Categories are hierarchical (like standard WordPress post categories), so you can nest sub-categories under parent ones if needed.

### Shortcode

Place `[events_list]` on any page or post to embed the upcoming events list.

| Attribute | Default | Description |
|---|---|---|
| `category` | *(all)* | Comma-separated category slugs to filter by |

Examples:
```
[events_list]
[events_list category="music"]
[events_list category="music,workshops,community"]
```

### Creating events

Go to **Events > Add New Event** in wp-admin. Fill in:

| Field | Required | Notes |
|---|---|---|
| Title | Yes | The event name |
| Description | No | Full description (post editor body) |
| Featured Image | No | Shown on cards and single event page |
| Date | Yes | YYYY-MM-DD — events before today are hidden automatically |
| Time | No | Displayed alongside the date |
| Location | No | Free text, e.g. "Saltburn Community Hall" |

### Query behaviour

- Only upcoming events are shown (today or later).
- Events are sorted ascending by date (soonest first).
- Both the archive and shortcode use the same query logic.

## Extension hooks

All six hooks from the core architecture are wired up. Future RSVP, member-gating, and payment extensions hook into these without modifying this plugin:

| Hook | Type | Purpose |
|---|---|---|
| `events_plugin_event_query_args` | filter | Modify the WP_Query args for all event lists |
| `events_plugin_can_view_event` | filter | Return false to hide an event from the current user |
| `events_plugin_event_display_data` | filter | Transform event data before rendering |
| `events_plugin_can_rsvp` | filter | Return true to show the RSVP UI (future extension) |
| `events_plugin_rsvp_form_fields` | filter | Inject RSVP form fields (future extension) |
| `events_plugin_rsvp_created` | action | Fires after a successful RSVP (future extension) |

## Test data

```bash
# From your WordPress root:
wp eval-file wp-content/plugins/events-plugin/bin/seed-test-events.php

# Clean up:
wp post delete $(wp post list --post_type=event --format=ids) --force
```

## File structure

```
events-plugin/
├── events-plugin.php              Main plugin file
├── includes/
│   ├── class-event-post-type.php  CPT registration
│   ├── class-event-meta-box.php   Admin meta box (date/time/location)
│   └── class-event-query.php      Query, rendering, shortcode, template loading
├── templates/
│   ├── archive-event.php          Events list page
│   └── single-event.php           Single event page
├── assets/
│   └── events-plugin.css          Base styles
└── bin/
    └── seed-test-events.php       WP-CLI test data seeder
```
