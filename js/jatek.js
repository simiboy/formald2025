let hangman = null;

function startHangmanGame() {
    const words = ["ÁRVÍZTŰRŐTÜKÖRFÚRÓGÉP", "MAGYARORSZÁG", "KÖZLEKEDÉS", "SZÁMÍTÓGÉP", "RENDSZERVÁLTÁS"];
    const word = words[Math.floor(Math.random() * words.length)];

  hangman = {
    word,
    guessed: Array(word.length).fill("_"),
    lives: 9,
    tried: new Set(),
    isGameOver: false,
  };

  printHangmanStatus("Játék elindítva, írj be egy betűt!");
}

// Handle letter guesses in hangman
function handleHangmanGuess(letter) {
  if (!hangman || hangman.isGameOver) return;

  letter = letter.toUpperCase();
  if (hangman.tried.has(letter)) {
    printHangmanStatus(`Már próbáltad a(z) "${letter}" betűt.`);
    return;
  }

  hangman.tried.add(letter);

  let hit = false;
  for (let i = 0; i < hangman.word.length; i++) {
    if (hangman.word[i] === letter) {
      hangman.guessed[i] = letter;
      hit = true;
    }
  }

  if (!hit) hangman.lives--;

  if (hangman.guessed.join("") === hangman.word) {
    printHangmanStatus(`Gratulálok! Kitaláltad: ${hangman.word}`);
    hangman.isGameOver = true;
  } else if (hangman.lives <= 0) {
    printHangmanStatus(`Vesztettél! A szó: ${hangman.word}`);
    hangman.isGameOver = true;
  } else {
    printHangmanStatus();
  }
}

function printHangmanStatus(customMessage = null) {
  const statusLine = document.createElement("div");
  statusLine.className = "line";

  const display = hangman.guessed.join(" ");
  const livesLeft = hangman.lives;
  const tried = Array.from(hangman.tried).join(", ");
  var message = "";
  if (customMessage) message += customMessage + "<br>";
  message += `${display} (<span style="color: var(--red)">${livesLeft}/9 életed maradt</span>). Próbált betűk: ${tried}`;

  statusLine.innerHTML = message;
  terminal.insertBefore(statusLine, inputLine);
  scrollToBottom();

  // === Load panel content dynamically ===
  const wrongGuesses = 9 - hangman.lives;
  const panel = document.getElementById("hangman-panel");

  fetch(`/assets/jatek/${wrongGuesses}.txt`)
    .then(response => {
      if (!response.ok) throw new Error("Nem található fájl");
      return response.text();
    })
    .then(text => {
      const html = text
        .split('\n')
        .map(line => line.replace(/ /g, '&nbsp;') + '<br>')
        .join('');
      panel.innerHTML = `<pre>${html}</pre>`;
    })
    .catch(error => {
      panel.innerHTML = `<p style="color:red;">Nem található: ${wrongGuesses}.txt</p>`;
    });
}
