const form = document.querySelector('.form');
const notification = document.querySelector('.card__header');
const inputs = document.querySelectorAll('input');
const submitButton = document.querySelector('button[type="submit"]');
const channelInput = document.querySelector('#channel');
const btsTable = document.querySelector('.bts-list-body');
const logoAnimation = document.querySelector('.logo__radiation');
let mapLayer;

function toggleInputs() {
  inputs.forEach((input) => { input.disabled = !input.disabled; });
  submitButton.toggleAttribute('disabled');
  channelInput.toggleAttribute('disabled');
}

function displayNotification(color = 'info', text = '', blockInputs = false) {
  notification.innerHTML = text;
  notification.classList.remove('bg-danger', 'bg-info', 'bg-success');
  notification.classList.add(`bg-${color}`, 'card__header--show');
  if (blockInputs) toggleInputs();
  setTimeout(() => {
    notification.classList.remove('card__header--show');
  }, 3000);
}

function displayBtsList() {
  fetch('http://dominik.sucharski.student.put.poznan.pl/?action=GetUserList', { method: 'GET' })
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
        const btsList = JSON.parse(result);
        btsTable.innerHTML = '';
        let htmlElement = '';
        const markers = [];
        const btsCoords = [];
        btsList.forEach((el) => {
          htmlElement += `
          <tbody class="bts-list-body">
          <tr>
            <th scope="row" class="align-middle">${el.user_id}</th>
            <td class="align-middle text-right">${el.user_name}</td>
            <td class="align-middle text-right">${el.user_coords_x}</td>
            <td class="align-middle">${el.user_coords_y}</td>
            <td class="align-middle">${el.user_ptx}</td>
            <td class="align-middle">${el.user_channel}</td>
            <td class="text-danger align-middle text-center"><span class="remove" data-index=${el.user_id}  title="Usuń użytkownika z listy">╳</span></td>
          </tr>
        </tbody>`;
          btsCoords.push([+el.user_coords_x, +el.user_coords_x]);
          markers.push(L.marker([el.user_coords_x, el.user_coords_y]).bindPopup(`Moc: ${el.user_ptx} [dBm] | Kanał: ${el.user_channel}`));
        });
        mapLayer = L.layerGroup([...markers]);
        mapLayer.addTo(mymap);
        btsTable.innerHTML = htmlElement;
      }
    });
}

function removeUser(e) {
  if (!e.target.matches('.remove')) return; // skip everything unless it's an remove button
  fetch(`http://dominik.sucharski.student.put.poznan.pl/?action=DeleteUser&id=${e.target.dataset.index}`, { method: 'GET' })
    .then((response) => response.text())
    .then((result) => {
      if (result === '1') {
        displayNotification('success', 'Usunięto użytkownika.');
        mapLayer.clearLayers();
        displayBtsList();
      } else {
        displayNotification('danger', 'Błąd podczas usuwania użytkownika!');
      }
    });
}

function handleResponse(result) {
  switch (result.response) {
    case 'python_error':
      displayNotification('danger', 'Błąd obliczeń.', true);
      break;
    case 'no_access':
      displayNotification('info', 'Brak dostępu dla użytkownika.', true);
      break;
    case 'set_all_params':
      displayNotification('danger', 'Nie ustawiono wszystkich parametrów lub są one błędne.', true);
      break;
    case 'user_exist':
      displayNotification('info', 'Istnieje użytkownik o podanych parametrach.', true);
      break;
    default:
      displayNotification('success', 'Przyznano dostęp.', true);
  }
  displayBtsList();
  logoAnimation.style.display = 'none';
}

function addBTS(latitude, longitude, power, channel, username) {
  const formdata = new FormData();
  formdata.append('user_name', username);
  formdata.append('power', power);
  formdata.append('coord_x', latitude);
  formdata.append('coord_y', longitude);
  formdata.append('channel', channel);
  fetch('http://dominik.sucharski.student.put.poznan.pl?action=AddUser', { method: 'POST', body: formdata })
    .then((response) => response.text())
    .then((result) => {
      handleResponse(JSON.parse(result));
    });
}

function handleData(e) {
  e.preventDefault();
  toggleInputs();
  logoAnimation.style.display = 'block';

  const latitude = this.querySelector('#latitude').value;
  const longitude = this.querySelector('#longitude').value;
  const power = this.querySelector('#power').value;
  const channel = this.querySelector('#channel').value;
  const name = this.querySelector('#username').value;

  addBTS(latitude, longitude, power, channel, name);
  form.reset();
}

form.addEventListener('submit', handleData);
document.addEventListener('DOMContentLoaded', displayBtsList);
btsTable.addEventListener('click', removeUser);
