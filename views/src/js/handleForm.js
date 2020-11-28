let btsList;

const form = document.querySelector('.form');
const notification = document.querySelector('.card__header');
const inputs = document.querySelectorAll('input');
const submitButton = document.querySelector('button[type="submit"]');
const btsTable = document.querySelector('.bts-list-body');
// const usersRemoveButtons = document.querySelectorAll('.remove');

function displayBtsList() {
  const requestOptions = {
    method: 'GET',
    redirect: 'manual',
  };

  fetch('http://dominik.sucharski.student.put.poznan.pl/?action=GetUserList', requestOptions)
    .then((response) => response.text())
    .then((result) => {
      if (result === 'no_users') {
        btsTable.innerHTML = `
        <tbody class='bts-list-body'>
          <tr>
            <td colspan="7" class="text-center" >Brak użytkowników w systemie.</td>
          </tr>
        </tbody>
        `;
      } else {
        btsList = JSON.parse(result);
        btsTable.innerHTML = '';
        let htmlElement = '';
        btsList.forEach((el) => {
          htmlElement += `
          <tbody class="bts-list-body">
          <tr>
            <th scope="row" class="align-middle">${el.user_id}</th>
            <td class="align-middle text-right">${el.user_coords_x}</td>
            <td class="align-middle">${el.user_coords_y}</td>
            <td class="align-middle">${el.user_ptx}</td>
            <td class="align-middle">${el.user_channel}</td>
            <td class="align-middle">${el.user_points}</td>
            <td class="text-danger align-middle text-center"><span class="remove" data-index=${el.user_id}  title="Usuń użytkownika z listy">✕</span></td>
          </tr>
        </tbody>
          `;
        });
        btsTable.innerHTML = htmlElement;
      }
    });
}

function removeUser(e) {
  if (!e.target.matches('.remove')) return; // skip this unless it's an remove button
  const requestOptions = {
    method: 'GET',
    redirect: 'follow',
  };

  fetch(`http://dominik.sucharski.student.put.poznan.pl/?action=DeleteUser&id=${e.target.dataset.index}`, requestOptions)
    .then((response) => response.text())
    .then((result) => {
      if (result === '1') {
        notification.innerHTML = 'Usunięto użytkownika.';
        notification.classList.remove('bg-danger', 'bg-info', 'bg-success');
        notification.classList.add('bg-success', 'show');
        setTimeout(() => {
          notification.classList.remove('show');
        }, 3000);
        displayBtsList();
      } else {
        notification.innerHTML = 'Błąd podczas usuwania użytkownika!';
        notification.classList.remove('bg-danger', 'bg-info', 'bg-success');
        notification.classList.add('bg-danger', 'show');
        setTimeout(() => {
          notification.classList.remove('show');
        }, 3000);
      }
    });
}

function handleResponse(result) {
  switch (result.response) {
    case 'python_error':
      notification.innerHTML = 'Błąd obliczeń.';
      notification.classList.remove('bg-info', 'bg-success');
      notification.classList.add('bg-danger', 'show');
      break;
    case 'no_access':
      notification.innerHTML = 'Brak dostępu dla użytkownika.';
      notification.classList.remove('bg-danger', 'bg-success');
      notification.classList.add('bg-info', 'show');
      break;
    case 'set_all_params':
      notification.innerHTML = 'Nie ustawiono wszystkich parametrów lub są one błędne.';
      notification.classList.remove('bg-danger', 'bg-success');
      notification.classList.add('bg-info', 'show');
      break;
    case 'user_exist':
      notification.innerHTML = 'Istnieje użytkownik o podanych parametrach.';
      notification.classList.remove('bg-danger', 'bg-success');
      notification.classList.add('bg-info', 'show');
      break;
    default:
      notification.innerHTML = 'Przyznano dostęp.';
      notification.classList.remove('bg-danger', 'bg-info');
      notification.classList.add('bg-success', 'show');
  }
  displayBtsList();
  setTimeout(() => {
    inputs.forEach((input) => { input.disabled = false; });
    submitButton.toggleAttribute('disabled');
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
document.addEventListener('DOMContentLoaded', displayBtsList);
btsTable.addEventListener('click', removeUser);
