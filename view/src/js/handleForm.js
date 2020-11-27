const form = document.querySelector('.form');
const notification = document.querySelector('.card__header');
const inputs = document.querySelectorAll('input');
const submitButton = document.querySelector('button[type="submit"]');

// function hideElement(class) {

// }

function handleResponse(result) {
  if (result.response === 'python_error') {
    notification.innerHTML = 'Błąd obliczeń.';
    notification.classList.remove('bg-info', 'bg-success');
    notification.classList.add('bg-danger', 'show');
  } else if (result.response === 'no_access') {
    notification.innerHTML = 'Brak dostępu dla użytkownika.';
    notification.classList.remove('bg-danger', 'bg-success');
    notification.classList.add('bg-info', 'show');
  } else if (result.response === 'Podaj wszystkie parametry') {
    notification.innerHTML = 'Podaj wszystkie parametry';
    notification.classList.remove('bg-danger', 'bg-success');
    notification.classList.add('bg-info', 'show');
  } else {
    notification.innerHTML = 'Przyznano dostęp.';
    notification.classList.remove('bg-danger', 'bg-info');
    notification.classList.add('bg-success', 'show');
  }
  inputs.forEach((input) => { input.disabled = false; });
  submitButton.toggleAttribute('disabled');
  setInterval(() => {
    notification.classList.remove('show');
  }, 2000);
}

function addBTS(latitude, longitude, power, channel) {
  const formdata = new FormData();
  formdata.append('power', power);
  formdata.append('coord_x', latitude);
  formdata.append('coord_y', longitude);
  formdata.append('channel', channel);

  const requestOptions = {
    method: 'POST',
    body: formdata,
    redirect: 'follow',
  };

  fetch('http://dominik.sucharski.student.put.poznan.pl?action=AddUser', requestOptions)
    .then((response) => response.text())
    .then((result) => {
      handleResponse(JSON.parse(result));
    });
}

function handleData(e) {
  e.preventDefault();
  inputs.forEach((input) => { input.disabled = true; });
  submitButton.toggleAttribute('disabled');
  const latitude = this.querySelector('#latitude').value;
  const longitude = this.querySelector('#longitude').value;
  const power = this.querySelector('#power').value;
  const channel = this.querySelector('#channel').value;

  addBTS(latitude, longitude, power, channel);

  form.reset();
}

form.addEventListener('submit', handleData);
