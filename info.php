<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <title>StockExperience - Informacje</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
  <div class="form">
          <table width=100%>
            <tr>
                <td width=110px><img src="icon.png"/ style="width:100px;height:100px;"></td>
                <th><h1>Informacje</h1></th> 
                <th width=18% ><a href="profile.php"><button class="button-logout" name="comeback"/>Powrót</button></a></th>
                <th width=18% ><a href="terms.php"><button class="button-logout" name="terms"/>Regulamin</button></a></th>
            </tr>
          </table>
  </div>
  <div class="form">
    <h1>Jak używać StockExperience</h1>
    <ol>
    <li style="margin:0; text-align:left; color:#fafafa">Po rejestracji i aktywacji konta zobaczysz listę wszystkich dostępnych indeksów</li>
    <li style="margin:0; text-align:left; color:#fafafa">Znajdziesz tam: Nazwę indeksu, jego cenę, zmianę procentową, ostatnią aktualizacje i posiadaną przez ciebie ilość</p>
    <li style="margin:0; text-align:left; color:#fafafa">Aby indeks kupić/sprzedać należy w ramce przy nim podać ilość którą chcemy obrócić i kliknąć odpowiedni przycisk</p>
    <li style="margin:0; text-align:left; color:#fafafa">Aplikacja automatycznie sprawdzi czy możemy wykonać daną transakcje i jeśli tak, zmieni odpowiednie wartości</p>
    <li style="margin:0; text-align:left; color:#fafafa">Po kliknięciu na nazwę indeksu zostaniemy przekierowani na stronę ze szczegółowymi informacjami dotyczącymi jego</p>
    </ol>
    <h2>Jak widać, używanie aplikacji nie jest skomplikowane. Zatem - do roboty!</h2>
  </div>
  <div class="form">
    <h1>O Twórcy</h1>
    <h2>Maciej Mikołajek - Senior Software Developer - <a href="https://github.com/BinarySoftware/">@BinarySoftware</a></h2>
    <h4>Uczeń klasy o profilu matematyczno-fizycznym w III Społecznym Liceum Ogólnokształcącym w Krakowie. Samodzielnie rozwija swoją pasję - programowanie, od 9 rż. Jest autorem wielu projektów, w tym systemu hotelarskiego, aplikacji dla dealerów samochodów, wielu witryn i rozwiązań informatycznych. Dwukrotny laureat OI. Oprócz ewidentnych pasji wokół przedmiotów ścisłych uwielbia sporty indywidualne - w szczególności narciarstwo, którego jest instruktorem.</h4>
    <h3>Proszę o zadawanie pytań oraz raportowania błędów czy informowanie o chęci współpracy</h3>
    <h3>Projekt rozwijany na GitHubie: <a href="https://github.com/BinarySoftware/StockExperience/">GitHub - StockExperience</a></h3>
  </div>
  <div class="form">
    <h1>Changelog</h1>
    <h4>2(B5) (07.12) - Performance: 53x mniejszy plik, 7,3x szybsze ładowanie, dodane narzędzia deweloperskie</h4>
    <h4>2(B4) (04.12) - Poprawa logo, oddzielenie backendu od frontendu, podsumowanie poczynań, refaktoryzacja, Regulamin</h4>
    <h4>2(B3) (26.11) - Dodano informacje o wartości posiadanych indeksów, Nowe logo, Regulamin strony </h4>
    <h4>2(B2) (25.11) - Zmiana wyglądu strony, nowy CSS</h4>
    <h4>2(B1) (24.11) - Zmiana całego parsera, refaktoryzacja, poprawa stabilności</h4>
    <h4>1.9.9 (23.11) - Zmiany w indeksach, zmiana domeny, poprawki</h4>
    <h4>1.3.0 (11.05) - Aktualizacja zmiennych i treści, zmiana wizualnego aspektu systemu kupna/sprzedaży</h4>
    <h4>1.2.0 (09.05) - Dodanie przycisku "Odśwież" oraz strony "Informacje", aktualizacja zmiennych i treści</h4>
    <h4>1.1.0 (09.05) - Poprawa zauważonych błędów, naprawa układu strony oraz rzeczy wizualnych</h4>
    <h4>1.0.0 (08.05) - Uruchomienie aplikacji i udostępnienie pierwszej grupie</h4>
    <h4>0.5.0 (28.04) - Dodanie akcji i algorytmu kupna oraz sprzedaży</h4>
    <h4>0.4.0 (22.04) - Dodanie strony profilu użytkownika, systemu logowania, rejestracji i aktywacji</h4>
    <h4>0.3.0 (12.04) - Wersja Alpha grafiki dołączona</h4>
    <h4>0.2.0 (03.04) - Dodanie systemu logowania</h4>
    <h4>0.1.0 (02.04) - Uruchomienie serwera</h4>
  </div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="js/index.js"></script>
</body>
</html>