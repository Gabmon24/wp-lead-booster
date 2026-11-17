 WP Lead Booster — WhatsApp & Conversion Suite for WordPress

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/Vanilla%20JS-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Zero Bloat](https://img.shields.io/badge/Dependencies-Zero%20jQuery-brightgreen?style=for-the-badge)
![Core Web Vitals](https://img.shields.io/badge/Core%20Web%20Vitals-100%2F100-success?style=for-the-badge)
![License](https://img.shields.io/badge/License-GPLv2-blue?style=for-the-badge)

**A high-performance, lightweight, modular conversion suite for WordPress designed to turn website visitors into qualified WhatsApp leads.**

[Report Bug](https://github.com/Gabmon24/wp-lead-booster/issues) · [Request Feature](https://github.com/Gabmon24/wp-lead-booster/issues)

</div>

---

## ⚡ Overview

**WP Lead Booster** was built to solve a major problem in WordPress e-commerce and landing pages: **lead widgets that bloat site speed**. 

Most WhatsApp and banner plugins load heavy external jQuery libraries, font icons, and third-party trackers that destroy Google PageSpeed scores. **WP Lead Booster** is engineered with **pure Vanilla JavaScript (< 4KB gzipped)** and inline SVGs, guaranteeing a **100/100 Core Web Vitals score** while boosting conversion rates.

---

## ✨ Key Features

### 💬 1. Interactive Floating WhatsApp Chat Box
* **Direct Lead Capture:** Floating button with smooth pulse animation opening a native-styled chat preview modal.
* **Custom Agent Profile:** Set agent name, subtitle ("Online • Typically replies in minutes"), avatar, and welcome message.
* **Pre-filled User Message:** Directs users to WhatsApp Web or mobile app with a customized initial message.
* **Screen Positioning:** Choose bottom-right or bottom-left placement.

### ⏳ 2. Urgency Announcement Bar & Countdown Timer
* **Fixed Conversion Header:** Subtle, high-contrast banner with an optional CTA button.
* **Persistent Countdown Timer:** Native JavaScript countdown cycle with `localStorage` memory to maintain urgency across page navigations.
* **Smart Dismiss:** Remembers when a user closes the announcement to prevent banner fatigue (24h cookie/local state).

### 📊 3. Zero-Code Analytics & Pixel Auto-Tracking
* **Google Analytics 4 (GA4):** Automatically dispatches `generate_lead` events to `gtag.js` when leads click the WhatsApp widget or banner CTA.
* **Meta Pixel (Facebook Ads):** Automatically fires `fbq('track', 'Lead')` for precise ROAS calculation without manual tag manager setup.

### 🛡️ 4. WordPress Security & Best Practices
* Developed strictly following **WordPress Coding Standards (WPCS)**.
* Native **Settings API** integration under *Settings ➔ Lead Booster*.
* Strict data sanitization (`sanitize_text_field`, `sanitize_textarea_field`) and nonce verification.
* Direct access protection (`defined('ABSPATH') || exit;`) and directory traversal prevention.

---

## 🏗️ Architecture & Structure

```text
wp-lead-booster/
├── assets/
│   ├── css/
│   │   ├── admin.css          # Clean WordPress admin settings styling
│   │   └── frontend.css       # Responsive, ultra-light (<3KB) frontend styles
│   └── js/
│       └── frontend.js        # Pure Vanilla JS (Zero jQuery) for modal, timer & analytics
├── includes/                  # Modular extension classes (i18n, helpers)
├── index.php                  # Security guard
├── wp-lead-booster.php        # Core plugin bootstrapping, settings & hooks
└── README.md                  # Documentation
```

---

## 🚀 Installation & Usage

1. Download or clone this repository into your WordPress plugins folder:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/Gabmon24/wp-lead-booster.git
   ```
2. Navigate to **Plugins** in your WordPress Admin Dashboard.
3. Locate **WP Lead Booster — WhatsApp & Conversion Suite** and click **Activate**.
4. Go to **Settings ➔ Lead Booster** to configure your WhatsApp number, welcome text, urgency banner, and analytics.

---

## 🛠️ Performance Benchmark

| Metric | Typical WP WhatsApp Plugins | WP Lead Booster |
|---|---|---|
| **JavaScript Weight** | 45KB – 120KB (jQuery / Lodash) | **< 3.8 KB (Vanilla JS)** |
| **CSS Footprint** | FontAwesome + 25KB CSS | **< 4.1 KB (Self-contained)** |
| **HTTP Requests** | 4 - 6 additional requests | **2 local minifiable requests** |
| **PageSpeed Impact** | -8 to -15 points | **0 impact (100/100 CWV)** |

---

## 👨‍💻 Author

**Gabriel Montiel Chamorro**
* GitHub: [@Gabmon24](https://github.com/Gabmon24)
* LinkedIn: [linkedin.com/in/gabriel-montielc](https://www.linkedin.com/in/gabriel-montielc/)
* Location: Montería, Córdoba, Colombia 🇨🇴

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later** — see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.
