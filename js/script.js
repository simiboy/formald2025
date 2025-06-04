const terminal = document.getElementById("terminal");
const input = document.getElementById("cmdInput");

// Our pseudo-DataFrame (Array of Objects)
const logData = [];

// Command handlers
const commands = {
  clear: {
    type: "function",
    output: () => {
      while (terminal.firstChild) terminal.removeChild(terminal.firstChild);
      terminal.appendChild(inputLine);
      return null;
    },
  },
  about: {
    type: "dynamic",
    output: async () => await loadFile("about.html"),
  },
  dijak: {
    type: "dynamic",
    output: async () => await loadFile("dijak.html"),
  },
  help: {
    type: "dynamic",
    output: async () => await loadFile("help.html"),
  },
  idozites: {
    type: "dynamic",
    output: async () => await loadFile("idozites.html"),
  },
  inspiracio: {
    type: "dynamic",
    output: async () => await loadFile("inspiracio.html"),
  },
  kiallitas: {
    type: "dynamic",
    output: async () => await loadFile("kiallitas.html"),
  },
  workshop: {
    type: "dynamic",
    output: async () => await loadFile("workshop.html"),
  },
  kapcsolat: {
    type: "dynamic",
    output: async () => await loadFile("kapcsolat.html"),
  },
  korabbi: {
    type: "dynamic",
    output: async () => await loadFile("korabbi_versenyek.html"),
  },
  letoltesek: {
    type: "dynamic",
    output: async () => await loadFile("letoltesek.html"),
  },
  zsuri: {
    type: "dynamic",
    output: async () => await loadFile("zsuri.html"),
  },
  nevezes: {
    type: "dynamic",
    output: async () => await loadFile("nevezes.html"),
  },
  gyik: {
    type: "dynamic",
    output: async () => await loadFile("gyik.html"),
  },
  english: {
    type: "dynamic",
    output: async () => await loadFile("english.html"),
  },
  jatek: {
    type: "function",
    output: () => {
      startHangmanGame();
      return null;
    },
  },
  color: {
    type: "function",
    output: changeBackgroundColor,
  },
};

// Reusable file loader function
async function loadFile(filename) {
  try {
    const res = await fetch(`pages/${filename}`);
    if (!res.ok) throw new Error("File not found");
    return await res.text();
  } catch (e) {
    return `Hiba a(z) "${filename}" fájl betöltésekor. Ellenőrizd, hogy létezik-e a fájl, és hogy webszerverről fut-e az oldal.`;
  }
}

// Handle command input
input.addEventListener("keydown", async (e) => {
  if (e.key === "Enter") {
    removeSuggestions();

    const raw = input.value.trim();
    const [cmd, ...args] = raw.split(" ");
    const fullCmd = cmd.toLowerCase();
    const param = args.join(" ");
    input.value = "";

    // Hangman override: if game is active and input is one letter
    if (
      hangman &&
      !hangman.isGameOver &&
      raw.length === 1 &&
      /^[a-záéíóöőúüűA-ZÁÉÍÓÖŐÚÜŰ]$/.test(raw)
    ) {
      handleHangmanGuess(raw);
      input.value = "";
      return;
    }

    const outputLine = document.createElement("div");
    outputLine.className = "line";
    outputLine.innerHTML = `&gt; ${raw}`;
    terminal.insertBefore(outputLine, inputLine);

    let response = "";
    let type = "";

    if (commands[fullCmd]) {
      const def = commands[fullCmd];
      type = def.type;

      if (def.type === "predefined") {
        response = def.output;
      } else if (def.type === "function") {
        response = def.output(param);
      } else if (def.type === "dynamic") {
        response = await def.output(param);
      }
    } else {
      type = "error";
      response = `Unknown command: ${raw}`;
    }

    if (response) {
      const respLine = document.createElement("div");
      respLine.className = "line";
      terminal.insertBefore(respLine, inputLine);

      // Parse the response as HTML
      const parser = new DOMParser();
      const doc = parser.parseFromString(response, "text/html");
      const nodes = Array.from(doc.body.childNodes);

      // Helper to delay
      const delay = (ms) => new Promise((res) => setTimeout(res, ms));

      async function typeNode(node, parent) {
        if (node.nodeType === Node.TEXT_NODE) {
          const words = node.textContent.match(/\S+\s*/g) || [];
          for (let word of words) {
            resetInactivityTimer();
            parent.appendChild(document.createTextNode(word));
            scrollToBottom();
            await delay(10);
          }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
          const clone = node.cloneNode(false); // clone the element without its children
          parent.appendChild(clone);
          scrollToBottom();

          for (let child of node.childNodes) {
            await typeNode(child, clone); // recurse into children
          }
        }
      }

      (async () => {
        for (let node of nodes) {
          await typeNode(node, respLine);
        }
        if (fullCmd === "gyik") {
          setupGyik();
        }
      })();
    }

    // Log to pseudo-DataFrame
    logData.push({
      input: raw,
      type: type,
      output: typeof response === "string" ? response : "",
    });
    console.log(logData);
  }
});

const scrollToBottom = () => {
  window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
};

// Keep input line always last
const inputLine = document.querySelector(".input-line");

document.body.addEventListener("click", (e) => {
  // If the clicked element is NOT a link (<a>)... or not a gyik element
  if (
    (e.target.tagName.toLowerCase() !== "a") &
    (e.target.className !== "question selected")
  ) {
    input.focus();
  }
});

// SUGGESTIONS

const container = document.getElementById("suggestions");
let inactivityTimer = null;

function showSuggestions() {
  if (container.innerHTML != "") return;

  const suggestions = getRandomSuggestions();

  suggestions.forEach((cmd) => {
    const span = document.createElement("span");
    span.className = "suggestion";
    span.textContent = cmd;

    span.addEventListener("click", () => {
      input.value = cmd;

      // Simulate Enter key
      const enterEvent = new KeyboardEvent("keydown", {
        bubbles: true,
        cancelable: true,
        key: "Enter",
      });
      input.dispatchEvent(enterEvent);

      // Restart timer for next suggestions
      resetInactivityTimer();
    });

    container.appendChild(span);
  });
}

function removeSuggestions() {
  const container = document.getElementById("suggestions");
  container.innerHTML = "";
}

const getRandomSuggestions = () => {
  const keys = Object.keys(commands).filter((k) => k !== "help");
  for (let i = keys.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [keys[i], keys[j]] = [keys[j], keys[i]];
  }
  return ["help", ...keys.slice(0, 2 + Math.floor(Math.random() * 2))];
};

function resetInactivityTimer() {
  clearTimeout(inactivityTimer);
  inactivityTimer = setTimeout(showSuggestions, 5000);
  console.log("reset");
}

// Start timer on load
resetInactivityTimer();

// Reset timer on activity
["mousemove", "keydown", "mousedown", "touchstart"].forEach((event) =>
  document.addEventListener(event, resetInactivityTimer)
);

function handleHash() {
  const hash = window.location.hash;
  if (hash.startsWith("#") && hash.length > 1) {
    const command = decodeURIComponent(hash.substring(1));
    input.value = command;

    // Simulate pressing Enter (you can customize this as needed)
    const enterEvent = new KeyboardEvent("keydown", {
      key: "Enter",
      code: "Enter",
      keyCode: 13,
      which: 13,
      bubbles: true,
    });
    input.dispatchEvent(enterEvent);
  }
}

function changeBackgroundColor(param) {
  const color = param.trim();

  if (color.toLowerCase() === "default") {
    document.body.style.backgroundColor = "";
    document.body.style.color = "";
    return `A háttérszín visszaállítva az alapértelmezettre.`;
  }

  const isValidColor =
    /^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/.test(color) || /^[a-zA-Z]+$/.test(color);

  if (!isValidColor) {
    return `Érvénytelen szín: "${color}". Használj érvényes színnevet vagy hex kódot (#RRGGBB).`;
  }

  // Create a temporary element to compute the actual color
  const temp = document.createElement("div");
  temp.style.color = color;
  temp.style.display = "none";
  document.body.appendChild(temp);

  const computedColor = getComputedStyle(temp).color;
  document.body.removeChild(temp);

  // Extract RGB values
  const rgb = computedColor.match(/\d+/g).map(Number);
  const luminance = (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]);

  // Set background color
  document.body.style.backgroundColor = color;

  // Set text color for better contrast
  if (luminance < 128) {
    document.body.style.color = "white";
  } else {
    document.body.style.color = "black";
  }

  return `Szín megváltoztatva`;
}



// Run on initial load
window.addEventListener("DOMContentLoaded", handleHash);

// Also listen to hash changes (if # changes dynamically after load)
window.addEventListener("hashchange", handleHash);

