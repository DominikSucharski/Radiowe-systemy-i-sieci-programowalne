Autor: Dominik Sucharski
# Radiowe systemy i sieci programowalne - projekt
1. Elementy składowe systemu:
   1. Baza danych
   2. Interfejs użytkownika
      * Zapewnienie komunikacji z systemem 
      * GUI - nakładka graficzna na ten interfejs
   3. System dostępu do widma
      * tworzenie mapy pokrycia
      * wczytywanie parametrów
    4. Obliczanie dopuszczalnej mocy transmisyjnej
2. Metoda pracy
   * Agile - SCRUM
   * Właściciel produktu: prowadzący (przedstawia koncepcję, kształt i wymagania dotyczące finalnego produktu)
   * Zespół(-y) programistów: grupy wymienione w pkt. 3 (opracowują poszczególne elementy funkcjonalne produktu)
   * Sprint o długości: 1 tydzień
3. Grupy niezbędne do realizacji projektu
   1. Baza danych (liczba osób: 2) - grupa odpowiedzialna za utworzenie struktury bazy danych, umieszczenie jej na serwerze i zapewnienie integracji tej bazy z kontrolerem (SQL, mongoDB, postgreSQL; heroku, własna strona w domenie PUT, itp.)
   2. Kontroler (liczba osób: 2) - grupa odpowiedzialna za opracowanie algorytmu, który obsługuje zgłoszenia użytkownika, odpowiednio je przetwarzając, uruchamia system dostępu do widma, formuje odpowiednią odpowiedź zwrotną dla użytkownika. 
   3. Prosty interfejs użytkownika oraz graficzny interfejs użytkownika (liczba osób: 4) - grupa odpowiedzialna za utworzenie prostego interfejsu użytkownika umożliwiającego korzystanie z systemu oraz utworzenie nakładki graficznej na prosty interfejs użytkownika, aby umożliwić użytkownikowi korzystanie z systemu z poziomu przeglądarki.
   4. System dostępu do widma (liczba osób: 4) - grupa odpowiedzialna za utworzenie "serca" systemu, tj. wykonania niezbędnych obliczeń, które umożliwią wygenerowanie zgody (lub odmowy) na żądanie użytkownika. 
   5. Koordynator (liczba osób: 1) - nadzoruje pracę wszystkich pozostałych grup, jest odpowiedzialny za przeprowadzenie cotygodniowego spotkania (w dowolnej formie) podczas, którego przedstawione zostaną najważniejsze rzeczy do zrobienia i przedstawiony zostanie obecny status prac każdej z grup. Krótka notatka z takiego spotkania powinna być regularnie przesyłana do prowadzącego. Dodatkowym zadaniem koordynatora jest prowadzenie (nadzór nad) raportu w formie "wiki" jako instrukcji korzystania z systemu, wraz z dokładnym opisem implementacji.


**API:**
 * **?action=AddUser** - dodawanie użytkownika
   * zwracane wartości:
      * python_error - błąd w wykonaniu skryptu napisanego w pythonie lub brak połączenia z serwerem na któym znajduje sie ten skrypt
      * no_access - użytkownik o podanych parametrach nie ma dostępu
      * [tablica z wynikami] - użytkownik ma dostęp, został zwrócony wynik obliczeń
    * przykładowe zapytanie (GET lub POST)
        * http://dominik.sucharski.student.put.poznan.pl/?action=AddUser&user_name=testowy&coord_x=20&coord_y=40&power=12&channel=5&aclr_1=40&aclr_2=60
            * user_name - nazwa użytkownika
            * coord_x - współrzędna x [km]
            * coord_y - współrzędna y [km]
            * power - moc [dBm]
            * channel - numer kanału (1-10)
            * aclr_1 - ACLR [dB]
            * aclr_2 - ACLR [dB]
    * przykładowa odpowiedź:
        "[(17.0, 40.0), (18.0, 38.0), (18.0, 39.0), (18.0, 40.0), (18.0, 41.0), (18.0, 42.0), (19.0, 38.0), (19.0, 39.0), (19.0, 40.0), (19.0, 41.0), (19.0, 42.0), (20.0, 37.0), (20.0, 38.0), (20.0, 39.0), (20.0, 41.0), (20.0, 42.0), (20.0, 43.0), (21.0, 38.0), (21.0, 39.0), (21.0, 40.0), (21.0, 41.0), (21.0, 42.0), (22.0, 38.0), (22.0, 39.0), (22.0, 40.0), (22.0, 41.0), (22.0, 42.0), (23.0, 40.0)]"
* **?action=GetUserList** - zwraca listę użytkowników w formacie JSON
    * przykłądowa odpowiedź:
    [{"user_id":"1","user_name":"pierwszy user","user_coords_x":"15","user_coords_y":"15","user_channel":"1","user_ptx":"3","aclr_1":"45","aclr_2":"45","user_points":"[(14.0, 15.0), (15.0, 14.0), (15.0, 16.0), (16.0, 15.0)]"},{"user_id":"2","user_name":"moc: 10, kana\u0142: 1","user_coords_x":"15","user_coords_y":"20","user_channel":"2","user_ptx":"10","aclr_1":"25","aclr_2":"12","user_points":"[(13.0, 19.0), (13.0, 20.0), (13.0, 21.0), (14.0, 18.0), (14.0, 19.0), (14.0, 20.0), (14.0, 21.0), (14.0, 22.0), (15.0, 18.0), (15.0, 19.0), (15.0, 21.0), (15.0, 22.0), (16.0, 18.0), (16.0, 19.0), (16.0, 20.0), (16.0, 21.0), (16.0, 22.0), (17.0, 19.0), (17.0, 20.0), (17.0, 21.0)]"},{"user_id":"3","user_name":"asd","user_coords_x":"16","user_coords_y":"60","user_channel":"1","user_ptx":"15","aclr_1":"27","aclr_2":"8","user_points":"[(12.0, 59.0), (12.0, 60.0), (12.0, 61.0), (13.0, 57.0), (13.0, 58.0), (13.0, 59.0), (13.0, 60.0), (13.0, 61.0), (13.0, 62.0), (13.0, 63.0), (14.0, 57.0), (14.0, 58.0), (14.0, 59.0), (14.0, 60.0), (14.0, 61.0), (14.0, 62.0), (14.0, 63.0), (15.0, 56.0), (15.0, 57.0), (15.0, 58.0), (15.0, 59.0), (15.0, 60.0), (15.0, 61.0), (15.0, 62.0), (15.0, 63.0), (15.0, 64.0), (16.0, 56.0), (16.0, 57.0), (16.0, 58.0), (16.0, 59.0), (16.0, 61.0), (16.0, 62.0), (16.0, 63.0), (16.0, 64.0), (17.0, 56.0), (17.0, 57.0), (17.0, 58.0), (17.0, 59.0), (17.0, 60.0), (17.0, 61.0), (17.0, 62.0), (17.0, 63.0), (17.0, 64.0), (18.0, 57.0), (18.0, 58.0), (18.0, 59.0), (18.0, 60.0), (18.0, 61.0), (18.0, 62.0), (18.0, 63.0), (19.0, 57.0), (19.0, 58.0), (19.0, 59.0), (19.0, 60.0), (19.0, 61.0), (19.0, 62.0), (19.0, 63.0), (20.0, 59.0), (20.0, 60.0), (20.0, 61.0)]"},{"user_id":"4","user_name":"testowy","user_coords_x":"20","user_coords_y":"40","user_channel":"5","user_ptx":"12","aclr_1":"40","aclr_2":"60","user_points":"[(17.0, 40.0), (18.0, 38.0), (18.0, 39.0), (18.0, 40.0), (18.0, 41.0), (18.0, 42.0), (19.0, 38.0), (19.0, 39.0), (19.0, 40.0), (19.0, 41.0), (19.0, 42.0), (20.0, 37.0), (20.0, 38.0), (20.0, 39.0), (20.0, 41.0), (20.0, 42.0), (20.0, 43.0), (21.0, 38.0), (21.0, 39.0), (21.0, 40.0), (21.0, 41.0), (21.0, 42.0), (22.0, 38.0), (22.0, 39.0), (22.0, 40.0), (22.0, 41.0), (22.0, 42.0), (23.0, 40.0)]"}]
* **?action=ViewUserList** - wyświetla listę użytkowników w postaci tabeli (zastąpione przez graficzny interfejs użytkownika)
* **?action=DeleteUser** - usuwanie użytkownika
    * przykładowe zapytaie (GET lub POST)
        * http://dominik.sucharski.student.put.poznan.pl/?action=DeleteUser&id=3
            * id - id użytkownika
    * zwraca 1 gdy udało się usunąć użytkownika, 0 gdy użytkownik nie istnieje lub wystąpił inny błąd
* **?action=GetSystemParams** - zwraca parametry systemu zapisane w bazie danych w postaci JSON
    * przykładowa odpowiedź:
    [{"name":"bandwidth","value":"10000000"},{"name":"carrier_frequency","value":"2.5"},{"name":"matrix_length","value":"200"},{"name":"min_power","value":"0"},{"name":"points_spacing","value":"1"},{"name":"power_reduction_step","value":"3"}]


**Mechanizm redukcji mocy i zmiany kanału:**
1. Próba uzyskania dostępu z początkowymi parametrami
2. Zmniejszenie mocy o wartość power_reduction_step zapisaną w bazie danych
3. Moc jest zmniejszana do czasu uzyskania dostępu lub osiągniecią mocy minimalnej (min_power)
4. Jeżeli wciąż brak dostępu moc jest przywracana do wartości początkowej, natomiast kanał jest zwiększany o 1 względem kanału początkowego
5. Krok 4 jest powtarzany naprzemian zmniejszając i zwiększając odległość od kanału początkowego do czasu, aż zostanie przyznany dostep lub zostanie wyczerpany limit prób.
Przykładowe działanie algorytmu:
    1. moc 10 dBm, kanał 4
    2. moc 5 dBm, kanał 4
    3. moc 0 dBm, kanał 4
    4. moc 10 dBm, kanał 5
    5. moc 5 dBm, kanał 5
    6. moc 0 dBm, kanał 5
    7. moc 10 dBm, kanał 3
    8. moc 5 dBm, kanał 3
    9. moc 0 dBm, kanał 3
    10. moc 10 dBm, kanał 6


**Droga danych z formularza**
1. Dane z formularza wysyłane są przez POST do kontrolera.
2. Kontroler odczytuje typ akcji i dane oraz wykonuje ich konwersje na int, float lub string.
3. Kontroler sprawdza czy istnieje już użytkownik o podanych współrzędnych i kanale.
    * jeżeli istnieje zwracany jest odpowiedni komunikat po czym system kończy działanie
4. Pobierane są dane istniejących użytkowników.
5. Zgodnie z algorytmem redukcji mocy i zmiany kanału wywoływany jest skrypt wykonujący obliczenia napisany w Pythonie.
6. Skrypt napisany w pythonie zwraca wynik obliczeń - brak dostępu lub tablicę z punktami.
    * w przypadku dostępu użytkownik jest zapisywany w bazie
7. Kontroler zwraca informację o dostępie lub braku dostępu do interfejsu.
8. Interfejs wyświetla komunikat oraz odświeża widok gdy użytkownik ma przyznany dostęp