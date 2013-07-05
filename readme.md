# Mothership CMS

The `Message\Mothership\CMS` cogule provides a Content Management System for Mothership.

## Markdown Parsing

This cogule provides a [Markdown](http://daringfireball.net/projects/markdown/) parser as the service `markdown.parser`.

Currently, the parser we use is [`dflydev/markdown`](https://packagist.org/packages/dflydev/markdown).

## Routes

This cogule defines two route collections:

- **ms.cms**: The frontend where CMS pages should be renderd.
- **ms.cp.cms**: The backend where the CMS is administered, within the Mothership Control Panel.

## Dispatched Events

cms.page.create
	-> Page
	-> Result (success or failed)
cms.page.edit.save
	-> Page (before)
	-> Page (after)
	-> Result (success or failed)
cms.page.delete
	-> Page
	-> Result (success or failed)
cms.page.restore
	-> Page
	-> Result (success or failed)

### Editing start / end

This could be used to introduce locking of page while somebody is editing it.

cms.page.edit.start (when user has requested a page edit form)
cms.page.edit.finish (when the user has either navigated away, cancelled the form or committed the form)

### Commenting

cms.comment.create

cms.comment.approve

cms.comment.unapprove

cms.comment.report

cms.comment.delete


### Assets

asset.create

asset.delete

asset.edit

asset.use


**note: we could add a config option for the service name for the page type collection and user groups collection (so the app can swap them out if they like)**
