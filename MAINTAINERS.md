# Notes for maintainers

## Publishing a new release

1. Update the value of the `$pkgVersion` property in the main package `controller.php` file (for example `1.0.0`)
2. Create a git tag (for example `v1.0.0`)
3. Push the change and the tags to GitHub
4. Publish [a new GitHub Release](https://github.com/concrete5-community/redirect_by_browser_lang/releases)
5. The `create-release-attachment.yml` GitHub Action will shortly attach the package .zip file to it automatically
