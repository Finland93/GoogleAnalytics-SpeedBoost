# GoogleAnalytics-SpeedBoost
This plugin removes Google Analytics load and creates popup where user can choose collection or not, this will improve loading times on Lighthouse, PageSpeed Insights and also on GTMetrix because analytics.js script file is not loaded.

# Another way to use this
Go to your theme template files and lookup for header.php. Inside this file you need to add this above </head> tag:
```
<style>
#gdpr-popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  border-radius: 20px;
  z-index: 9999;
}

#gdpr-popup > div {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #fff;
  padding: 20px;
  display: flex;
  align-items: center; 
}

#accept-btn, #reject-btn {
  margin: 0 10px; 
}
</style>
```
Next step is to add this line below opening <body> tag:
```
<div id="gdpr-popup"><div><p>Our website uses cookies</p><button id="accept-btn">Accept</button><button id="reject-btn">Reject</button></div></div>
```

Then you need to go inside footer.php file where we are looking ending </body> tag and adding this script above it, REMEBER change your G-tag code on these two lines below: YOUR G-CODE HERE
```
<script>
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
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=YOUR G-CODE HERE';
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'YOUR G-CODE HERE');
  }

  const hasConsent = document.cookie.includes('AllowAnalytics=true');
  const hasRejected = document.cookie.includes('AllowAnalytics=false');

  if (!hasConsent && !hasRejected) {
    gdprPopup.style.display = 'block';
  } else if (hasConsent) {
    loadAnalyticsScript(); 
  }
});
</script>
```
