function setCookie(name, value, days) {
  let expires = '';
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = `; expires=${date.toUTCString()}`;
  }
  document.cookie = `${name}=${value || ''}${expires}; path=/`;
}

function getCookie(name) {
  const nameEQ = `${name}=`;
  const ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i += 1) {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

const themeSwitcher = document.querySelector('.theme-toggle img');
const wholeDocument = document.querySelector('html');

if (getCookie('theme-cookie') === 'dark') {
  themeSwitcher.src = './src/images/sun.svg';
  wholeDocument.classList.toggle('dark-theme');
}

themeSwitcher.addEventListener('click', () => {
  if (wholeDocument.classList.contains('dark-theme')) {
    themeSwitcher.src = './src/images/moon.svg';
    setCookie('theme-cookie', 'light', 7);
  } else {
    themeSwitcher.src = './src/images/sun.svg';
    setCookie('theme-cookie', 'dark', 7);
  }
  wholeDocument.classList.toggle('dark-theme');
});
