# Low Search Native for ExpressionEngine

Filters for [Low Search](http://gotolow.com/addons/low-search) that mimic some of [the native Channnel Entries filter parameters](https://ellislab.com/expressionengine/user-guide/add-ons/channel/channel_entries.html#parameters). *Requires Low Search 4+*.

## Installation

- Download and unzip;
- Copy the `low_search_native` folder to your `system/expressionengine/third_party` directory;
- All set!

## Usage

Low Search Native handles the following parameters:

- `author_id`
- `channel`
- `channel_id`
- `group_id`
- `show_expired`
- `show_future_entries`
- `status`
- `sticky`
- `url_title`
- `username`
- `year`
- `month`
- `day`

These work identically to their native counterparts, except for `channel_id`, which isn’t a native parameter; and `sticky`, which takes the additional values: `only` and `exclude`, to either only show sticky entries or exclude them altogether.

Normally, you wouldn’t need this filter, as the native Channel Entries tag takes care of it. But in some cases it can be a performance boost. YMMV.