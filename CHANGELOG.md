# Changelog

## 4.2.1

- Fix typo in `pinterest` config file (was `pintrest`)

## 4.2.0

- Added `Pending Comments` dashboard widget
- Added `Recently Approved Comments` dashboard widget
- Added `DashboardLoader` class in `Blog\Dashboard` namespace for loading appropriate comments and pages for dashboard
- Widgets will only display when a page type that extends `AbstractBlog` is registered
- Added `getUpdatedAt()` method to `Comment` object
- Added `getByStatus()` method to `CommentLoader`
- Added user group loader constructor argument to `CommentBuilder` class
- Automatic approval for comments made by super admins
- Updated CP dependency to 3.3

## 4.1.1

- Can no longer change parent and sibling of a page at the same time
- Task `cms:page:fix_tree` to rebuild page structure if the data has become malformed (i.e. as a result of a user changing the parent and sibling at the same time)
- Renamed "Siblings" in admin panel to "Sibling"
- Added `PageEditException` to be thrown when a page cannot be edited due to the nearest sibling not being found
- `changeParent()` and `changeOrder()` on `Page\Edit` class no longer catch exceptions and return false. They will always return a boolean of the result of the database transaction (which should always be true)
- Added help text for "Sibling" field
- Moved error messages to translation file
- Added error message for malformed nested set data
- Updated `Cog` dependency to 4.2

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
