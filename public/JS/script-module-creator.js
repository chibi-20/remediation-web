document.addEventListener("DOMContentLoaded", () => {
  const addQuestionBtn = document.getElementById("addQuestionBtn");
  const submitModuleBtn = document.getElementById("submitModuleBtn");
  const questionsContainer = document.getElementById("questionsContainer");

  let questionCount = 0;

  addQuestionBtn.addEventListener("click", () => {
    const qIndex = questionCount++;
    const block = document.createElement("div");
    block.className = "question-block";

    block.innerHTML = `
      <label>Question:</label>
      <input type="text" class="question-text" required />

      <div class="choices-container">
        <div class="choices" data-question-index="${qIndex}"></div>
        <button type="button" class="add-choice-btn">➕ Add Choice</button>
      </div>

      <button type="button" class="delete-question-btn">🗑 Delete Question</button>
    `;

    questionsContainer.appendChild(block);

    const choicesDiv = block.querySelector(".choices");
    const addChoiceBtn = block.querySelector(".add-choice-btn");
    const deleteQuestionBtn = block.querySelector(".delete-question-btn");

    let choiceCount = 0;

    const addChoice = () => {
      const choiceId = `${qIndex}-${choiceCount++}`;
      const choiceDiv = document.createElement("div");
      choiceDiv.style.marginTop = "0.5rem";
      choiceDiv.style.display = "flex";
      choiceDiv.style.alignItems = "center";
      choiceDiv.innerHTML = `
        <input type="radio" name="correct-${qIndex}" value="${choiceId}" style="margin-right: 6px;" />
        <input type="text" class="choice-text" data-id="${choiceId}" placeholder="Answer choice" required style="flex: 1;" />
        <button type="button" class="remove-choice-btn" style="margin-left: 6px;">❌</button>
      `;

      choiceDiv.querySelector(".remove-choice-btn").addEventListener("click", () => {
        choiceDiv.remove();
      });

      choicesDiv.appendChild(choiceDiv);
    };

    // Start with 2 choices
    addChoice();
    addChoice();

    addChoiceBtn.addEventListener("click", addChoice);

    deleteQuestionBtn.addEventListener("click", () => {
      block.remove();
    });
  });

  submitModuleBtn.addEventListener("click", async () => {
    const quarter = document.getElementById("quarter").value;
    const file = document.getElementById("pdf").files[0];

    if (!quarter || !file) {
      alert("Please select a quarter and upload a PDF.");
      return;
    }

    const questionBlocks = document.querySelectorAll(".question-block");
    const questions = [];

    for (const [index, block] of questionBlocks.entries()) {
      const text = block.querySelector(".question-text").value.trim();
      const choices = [];
      let correctAnswer = "";

      const choiceInputs = block.querySelectorAll(".choice-text");
      const radios = block.querySelectorAll(`input[type="radio"][name="correct-${index}"]`);

      choiceInputs.forEach((input) => {
        const value = input.value.trim();
        const id = input.dataset.id;
        if (value) {
          choices.push({ id, value });
        }
      });

      radios.forEach((radio) => {
        if (radio.checked) {
          correctAnswer = radio.value;
        }
      });

      if (!text || choices.length < 2 || !correctAnswer) {
        alert(`❗ Question ${index + 1} is incomplete. Make sure it has text, at least 2 choices, and one correct answer.`);
        return;
      }

      const correctText = choices.find(c => c.id === correctAnswer)?.value || "";
      questions.push({
        question: text,
        choices: choices.map(c => c.value),
        answer: correctText
      });
    }

    const formData = new FormData();
    formData.append("quarter", quarter);
    formData.append("pdf", file);
    formData.append("questions", JSON.stringify(questions));
    formData.append("adminId", localStorage.getItem("adminId") || 1);

    try {
      const response = await fetch("/api/create-module", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      if (result.success) {
        alert("✅ Module submitted successfully!");
        window.location.reload();
      } else {
        alert("❌ Submission failed: " + result.error);
      }
    } catch (err) {
      console.error("❌ Error submitting module:", err);
      alert("❌ Server error. Please try again.");
    }
  });
});
