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


API:
 * ?action=AddUser - dodawanie użytkownika
   * zwracane wartości:
      * python_error - błąd w wykonaniu skryptu napisanego w pythonie lub brak połączenia z serwerem na któym znajduje sie ten skrypt
      * no_access - użytkownik o podanych parametrach nie ma dostępu
      * <tablica z wynikami> - użytkownik ma dostęp, został zwrócony wynik obliczeń
    * przykładowe zapytanie (GET lub POST)
        * http://dominik.sucharski.student.put.poznan.pl/?action=AddUser&user_name=testowy&coord_x=20&coord_y=40&power=12&channel=5&aclr_1=40&aclr_2=60
            * user_name - nazwa użytkownika
            * coord_x - współrzędna x [km]
            * coord_y - współrzędna y [km]
            * power - moc [dBm]
            * channel - numer kanału
            * aclr_1 - ACLR [dB]
            * aclr_2 - ACLR [dB]
* ?action=GetUserList - zwraca listę użytkowników w formacie JSON
* ?action=ViewUserList - wyświetla listę użytkowników w postaci tabeli (zastąpione przez graficzny interfejs użytkownika)
* ?action=DeleteUser - usuwanie użytkownika
    * przykładowe zapytaie (GET lub POST)
        * http://dominik.sucharski.student.put.poznan.pl/?action=DeleteUser&id=3
            * id - id użytkownika
    * zwraca 1 gdy udało się usunąć użytkownika, 0 gdy użytkownik nie istnieje lub wystąpił inny błąd
* ?action=GetSystemParams - zwraca parametry systemu zapisane w bazie danych w postaci JSON 