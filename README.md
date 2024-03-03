# LatteAssetExtension

Extension for Latte v3 adding `n:asset` macro for simplification of asset render (css/js files mainly) in Latte

Using macro is simple, we just add attribute `n:asset` to commonly used HTML tags `<link>` or `<script>`.
Macro automatically generates main argument for linking asset URL like `<script src="...">` and adds other arguments, if needed - for instance it will respect if argument `rel=""` in linking css style is present already.
It will also prefix generated path by `{$basePath}` variable unless the linked URL is absolute.

For example when we use this in Latte:

```latte
<script n:asset="node_modules/jquery/dist/jquery.min.js"></script>
<link n:asset="node_modules/@fortawesome/fontawesome-free/css/all.css">
```

Output looks like this:

```html
<script type="text/javascript" src="/my/base/path/node_modules/jquery/dist/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="/my/base/path/node_modules/@fortawesome/fontawesome-free/css/all.css">
```

Registering is simple by using Latte Neon DI extension:

```neon
latte:
    extensions: Bilbofox\Latte\AssetExtension
```

## Formatters

Main purpose of this extension is formatters - which can further manipulate final render of asset.

Formatter is any callable that is registered into extension by calling.

```php
$assetExtension = new Bilbofox\Latte\AssetExtension;
$assetExtension->addFormatter(function (string $path): string {
    // ...

    return $path;
});
```

Callback receives original path as parameter and can modify it inside.

Library comes with existing formatters in form of invokable classes.

---

`Bilbofox\Latte\Formatters\VersionFormatter(int|string|callable $version)`
Adds version to asset in form of query parameter `?v=` at the end.

Version can be number, string or callable recieving original path.

---

`Bilbofox\Latte\Formatters\FileVersionFormatter(string $wwwDir)`
Extension of previous formatter which uses last modified timestamp of asset file as version - very useful when new assets files are deployed to production server - forces clients to reload browser cached assets.

Here we register extension with file version formatter:

```neon

latte:
    extensions:
         - @latteAssetExtension

latteAssetExtension:
    class: Bilbofox\Latte\AssetExtension
    setup:
        - addFormatter(Bilbofox\Latte\Formatters\FileVersionFormatter(%wwwDir%))
```

When we then use in Latte:

```latte
<script n:asset="node_modules/jquery/dist/jquery.min.js"></script>
```

We get something like this:

```html
<script type="text/javascript" src="/my/base/path/node_modules/jquery/dist/jquery.min.js?v=1708647873"></script>
```
