# Translation API

This extension lets you fetch localized labels from TYPO3. It will automatically
export labels as JSON from any `locallang.xlf` file, given its extension key.

## How-To Use within Fluid

This extension is able to export the labels as JSON to be embedded into a HTML snippet:

```
<div
	xmlns="http://www.w3.org/1999/xhtml" lang="en"
	xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
	xmlns:l10n="http://typo3.org/ns/Sinso/Translationapi/ViewHelpers"
>

    <!-- standard notation -->
    <l10n:exportXliff extensionKey="some-extension" prefix="some-prefix" />

    <!-- inline notation -->
    <section data-localized-days='{l10n:exportXliff(extensionKey:"some-extension", prefix:"some-prefix")}'>
        ...
    </section>

</div>
```

### Options

* `?omitPrefix=yes` (default "`no`") will strip the prefix from the key. E.g. with "module" as prefix,

  key "module.foo.bar" will be returned as "foo.bar"

* `?expand=yes` (default "`no`") will "expand" the keys as subarrays:

  ```
  {
      "module.foo.bar.key1": "value1",
      "module.foo.bar.key2": "value2",
  }
  ```

  becomes

  ```
  {
      "module": {
          "foo": {
              "bar": {
                  "key1": "value1",
                  "key2": "value2"
              }
          }
      }
  }
  ```
