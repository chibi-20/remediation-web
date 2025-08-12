const urlParams = new URLSearchParams(window.location.search);
const moduleId = urlParams.get("id");

const questionsContainer = document.getElementById("questionsContainer");
const form = document.getElementById("editModuleForm");

let moduleData = null;

async function loadModule() {
  const res = await fetch("/tms/remediation-web/api/modules.php");
  const modules = await res.json();
  moduleData = modules.find(m => m.id == moduleId);

  if (!moduleData) {
    alert("Module not found.");
    return;
  }

  // Set quarter
  document.getElementById("quarter").value = moduleData.quarter;

  // Render questions
  moduleData.questions.forEach((q, index) => {
    addQuestionBlock(q.question, q.choices, q.answer);
  });
}

function addQuestionBlock(text = "", choices = ["", "", "", ""], correct = "") {
  const questionDiv = document.createElement("div");
  questionDiv.className = "question-block";

  const questionIndex = questionsContainer.children.length + 1;
  questionDiv.innerHTML = `
    <h4>Question ${questionIndex}</h4>
    <label>Question Text:</label>
    <input type="text" class="question-text" value="${text}">

    <div class="choices"></div>
    <button type="button" class="add-choice-btn">➕ Add Choice</button>
    <button type="button" class="delete-question">🗑 Remove</button>
  `;

  const choicesDiv = questionDiv.querySelector(".choices");

  choices.forEach(choice => {
    const choiceRow = document.createElement("div");
    choiceRow.className = "choice-row";
    choiceRow.innerHTML = `
      <input type="radio" name="answer${questionIndex}" value="${choice}" ${choice === correct ? "checked" : ""}>
      <input type="text" value="${choice}">
      <button type="button">✖</button>
    `;
    choiceRow.querySelector("button").addEventListener("click", () => choiceRow.remove());
    choicesDiv.appendChild(choiceRow);
  });

  questionDiv.querySelector(".add-choice-btn").addEventListener("click", () => {
    const newChoiceRow = document.createElement("div");
    newChoiceRow.className = "choice-row";
    newChoiceRow.innerHTML = `
      <input type="radio" name="answer${questionIndex}" value="">
      <input type="text" value="">
      <button type="button">✖</button>
    `;
    newChoiceRow.querySelector("button").addEventListener("click", () => newChoiceRow.remove());
    choicesDiv.appendChild(newChoiceRow);
  });

  questionDiv.querySelector(".delete-question").addEventListener("click", () => questionDiv.remove());

  questionsContainer.appendChild(questionDiv);
}

document.getElementById("addQuestionBtn").addEventListener("click", () => {
  addQuestionBlock();
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const questions = [];
  const blocks = document.querySelectorAll(".question-block");

  for (let i = 0; i < blocks.length; i++) {
    const block = blocks[i];
    const text = block.querySelector(".question-text").value.trim();
    const choiceInputs = block.querySelectorAll(".choice-row input[type='text']");
    const radioInputs = block.querySelectorAll(".choice-row input[type='radio']");
    const choices = [];
    let answer = "";

    choiceInputs.forEach((input, index) => {
      const choiceVal = input.value.trim();
      if (choiceVal !== "") {
        choices.push(choiceVal);
        if (radioInputs[index].checked) {
          answer = choiceVal;
        }
      }
    });

    if (text && choices.length > 0 && answer) {
      questions.push({ question: text, choices, answer });
    }
  }

  const quarter = document.getElementById("quarter").value;
  const pdfFile = document.getElementById("pdf").files[0];
  const formData = new FormData();

  formData.append("id", moduleId);
  formData.append("quarter", quarter);
  formData.append("questions", JSON.stringify(questions));
  if (pdfFile) {
    formData.append("pdf", pdfFile);
  }

  const res = await fetch(`/tms/remediation-web/api/update-module.php?id=${moduleId}`, {
    method: "POST",
    body: formData
  });

  const result = await res.json();

  if (result.success) {
    alert("✅ Module updated!");
    window.location.href = "admin.html";
  } else {
    alert("❌ Failed to update: " + result.error);
  }
});

loadModule();
