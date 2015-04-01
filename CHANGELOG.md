# Changelog

## 4.1.0

- Share on social media (Twitter/Facebook only) controller
- Controller for linking to social media sites
- `social.yml` config file with support for usernames and slug overrides for Twitter, Facebook, Pinterest and Instagram
- Generic metadata view
- Can assign meta image to pages
- `meta_image` field on database
- Can set the order pages are loaded by in the page loader using the `orderBy()` method with constants from the `PageOrder` class
- Removed hard coded 4 character limit from page search, now uses whatever is in the `search.yml` config
- Deleted commented out code

## 4.0.1

- Search form displays in sidebar even if no pages exist
- Cookie trail pages sort by depth

## 4.0.0

- Initial open source release
