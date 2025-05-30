function setupGyik() {
    // Select the second-to-last .line element
    const lines = document.querySelectorAll(".line");
    const targetLine = lines[lines.length - 2];
    
    // Now select questions and display elements *inside* this line
    const questions = targetLine.querySelectorAll(".question");
    const questionDisplay = targetLine.querySelector("#question-display");
    const answerDisplay = targetLine.querySelector("#answer-display");
  
    let currentIndex = 0;
    let navigating = true;
  
    // Apply selected class
    function updateSelection() {
      questions.forEach((q, i) => {
        q.classList.toggle("selected", i === currentIndex);
      });
  
      const selected = questions[currentIndex];
      questionDisplay.textContent = selected.textContent;
      answerDisplay.textContent = selected.getAttribute("data-answer");
    }
  
    // Mouse events
    questions.forEach((q, index) => {
      q.addEventListener("mouseenter", () => {
        if (navigating) {
          currentIndex = index;
          updateSelection();
        } else {
          questionDisplay.textContent = q.textContent;
          answerDisplay.textContent = q.getAttribute("data-answer");
        }
      });
  
      q.addEventListener("mouseleave", () => {
        if (!navigating) {
          questionDisplay.textContent = "Válassz egy kérdést";
          answerDisplay.textContent = "Vidd az egeret egy kérdés fölé a válasz megtekintéséhez.";
        }
      });
    });
  
    // Keyboard navigation
    document.addEventListener("keydown", function handleKey(e) {
      if (!navigating) return;
  
      if (e.key === "ArrowDown" || e.key === "ArrowRight") {
        currentIndex = (currentIndex + 1) % questions.length;
        updateSelection();
      } else if (e.key === "ArrowUp" || e.key === "ArrowLeft") {
        currentIndex = (currentIndex - 1 + questions.length) % questions.length;
        updateSelection();
      } else if (e.key === "Enter") {
        navigating = false;
        questions.forEach(q => q.classList.remove("selected"));
        document.removeEventListener("keydown", handleKey); // Clean up
      }
    });
  
    // Start navigation
    navigating = true;
    currentIndex = 0;
    updateSelection();
  }
  