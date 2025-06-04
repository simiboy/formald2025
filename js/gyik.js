function setupGyik() {
  const lines = document.querySelectorAll(".line");
  const targetLine = lines[lines.length - 2];

  // Now select questions and display elements *inside* this line
  const questions = targetLine.querySelectorAll(".question");

  if (screen.width > 760) {
    // desktop version

    // Select the second-to-last .line element
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
          answerDisplay.textContent =
            "Vidd az egeret egy kérdés fölé a válasz megtekintéséhez.";
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
        questions.forEach((q) => q.classList.remove("selected"));
        document.removeEventListener("keydown", handleKey); // Clean up
      }
    });

    // Start navigation
    navigating = true;
    currentIndex = 0;
    updateSelection();
  } else {
    // mobile version
    questions.forEach((q, index) => {
      q.addEventListener("click", () => {
        // Delete all items with class "mobile-answer"
        document
          .querySelectorAll(".mobile-answer")
          .forEach((el) => el.remove());

        if (!q.classList.contains("selected")) {
          // Remove "selected" class from all questions
          questions.forEach((item) => item.classList.remove("selected"));
          // Add selected class to q
          q.classList.add("selected");

          // Create a div with the class "mobile-answer"
          const answerDiv = document.createElement("div");
          answerDiv.classList.add("mobile-answer");
          answerDiv.innerHTML = q.getAttribute("data-answer");

          // Insert the div after q
          q.parentNode.insertBefore(answerDiv, q.nextSibling);

          // scroll to it
          window.scrollTo({
            top: q.getBoundingClientRect().top + window.scrollY - 10,
            behavior: "smooth",
          });
        } else {
          // Remove "selected" class from all questions
          questions.forEach((item) => item.classList.remove("selected"));
        }
      });
    });
  }
}
