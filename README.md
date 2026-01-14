# Magento 2 Page Speed JS Extreme Lazy Load Frontend UI

[![Latest Stable Version](https://poser.pugx.org/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui/v/stable)](https://packagist.org/packages/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui)
[![Total Downloads](https://poser.pugx.org/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui/downloads)](https://packagist.org/packages/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui)
[![PayPal donate button](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=volodymyr%40hryvinskyi%2ecom&lc=UA&item_name=Magento%202%20Page%20Speed%20JS%20Extreme%20Lazy%20Load%20Frontend%20UI&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted "Donate once-off to this project using Paypal")
[![Latest Unstable Version](https://poser.pugx.org/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui/v/unstable)](https://packagist.org/packages/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui)
[![License](https://poser.pugx.org/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui/license)](https://packagist.org/packages/hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui)

## Description

Frontend implementation of JavaScript extreme lazy loading. Executes lazy load logic on storefront with JavaScript for event handling and script execution.

## Features

- **RequireJsConfigScriptMarker Plugin** - Marks RequireJS config scripts for special handling
- **Lazy Load Execution** - Frontend JavaScript for script lazy loading
- **Event Listeners** - Monitors user interactions to trigger script execution
- **Timeout Handlers** - Fallback script execution after delay
- **ViewModel** - Data providers for lazy load templates
- **API Interfaces** - Lazy loading operations

## Installation

```bash
composer require hryvinskyi/magento2-page-speed-js-extreme-lazy-load-frontend-ui
bin/magento module:enable Hryvinskyi_PageSpeedJsExtremeLazyLoadFrontendUi
bin/magento setup:upgrade
bin/magento cache:flush
```

## Dependencies

- `magento/framework: *`
- `hryvinskyi/magento2-page-speed-js-extreme-lazy-load: >=1.0.4`

## Compatibility

- Magento 2.3.x, 2.4.x
- PHP 7.4+, 8.0+, 8.1+

## License

[MIT License](LICENSE)

## Author

**Volodymyr Hryvinskyi** - volodymyr@hryvinskyi.com
