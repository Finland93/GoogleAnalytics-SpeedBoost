document.addEventListener('DOMContentLoaded', function () {
  const gdprPopup = document.getElementById('gdpr-popup');
  const acceptBtn = document.getElementById('accept-btn');
  const rejectBtn = document.getElementById('reject-btn');

  function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
  }

  function setCookieConsent() {
    setCookie('AllowAnalytics', 'true', 365); // Set the cookie to expire in a year
    gdprPopup.style.display = 'none';
    loadAnalyticsScript();
  }

  function rejectCookieConsent() {
    setCookie('AllowAnalytics', 'false', 365);
    gdprPopup.style.display = 'none';
  }

  acceptBtn.addEventListener('click', setCookieConsent);
  rejectBtn.addEventListener('click', rejectCookieConsent);

  function loadAnalyticsScript() {
    if (customGdprPopup.analyticsScript) {
      const script = document.createElement('script');
      script.async = true;
      script.src = 'https://www.googletagmanager.com/gtag/js?id=' + customGdprPopup.analyticsScript;
      document.head.appendChild(script);

      window.dataLayer = window.dataLayer || [];
      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());
      gtag('config', customGdprPopup.analyticsScript);
    }
  }

  const hasConsent = document.cookie.includes('AllowAnalytics=true');
  const hasRejected = document.cookie.includes('AllowAnalytics=false');

  if (!hasConsent && !hasRejected) {
    gdprPopup.style.display = 'block';
  } else if (hasConsent) {
    loadAnalyticsScript();
  }
});