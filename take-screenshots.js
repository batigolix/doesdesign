// AI generated
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    // Navigate to the home page
    console.log('Navigating to https://doesdesign.ddev.site/...');
    await page.goto('https://doesdesign.ddev.site/', { waitUntil: 'networkidle' });

    // Wait a couple seconds for dynamic content
    await page.waitForTimeout(2000);

    // Take full-page screenshot
    console.log('Taking screenshot of home page...');
    await page.screenshot({
      path: '/Users/boris/Sites/doesdesign/screenshot-denbei-theme.png',
      fullPage: true
    });
    console.log('Saved: /Users/boris/Sites/doesdesign/screenshot-denbei-theme.png');

    // Navigate to the mokume-gane page
    console.log('Navigating to https://doesdesign.ddev.site/mokume-gane...');
    await page.goto('https://doesdesign.ddev.site/mokume-gane', { waitUntil: 'networkidle' });

    // Wait a couple seconds for dynamic content
    await page.waitForTimeout(2000);

    // Take full-page screenshot
    console.log('Taking screenshot of mokume-gane page...');
    await page.screenshot({
      path: '/Users/boris/Sites/doesdesign/screenshot-denbei-mokume.png',
      fullPage: true
    });
    console.log('Saved: /Users/boris/Sites/doesdesign/screenshot-denbei-mokume.png');

  } catch (error) {
    console.error('Error taking screenshots:', error);
  } finally {
    // Always close the browser
    console.log('Closing browser...');
    await browser.close();
    console.log('Done!');
  }
})();
