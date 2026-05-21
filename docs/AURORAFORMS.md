# Aurora forms

AppstoreBundle ships pre-configured form theme styles compatible with Shoper's Aurora design system.

## Setup

Add the form theme to `config/packages/twig.yaml`:

```yaml
twig:
    form_themes:
        - '@Appstore/form.html.twig'
```

This applies Aurora-compatible styling to all Symfony forms rendered in your application's Twig templates.
