document.addEventListener("DOMContentLoaded", function () {
    const questionsContainer = document.getElementById("questions_container");
    const addQuestionButton = document.getElementById("add_question");
    let questionCount = 0;

    const quillConfigs = {
        modules: {
            toolbar: {
                container: [
                    ["bold", "italic", "underline", "strike"],
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    [{ list: "ordered" }, { list: "bullet" }],
                    [{ script: "sub" }, { script: "super" }],
                    [{ indent: "-1" }, { indent: "+1" }],
                    [{ direction: "rtl" }],
                    [{ font: [] }],
                    [{ align: [] }],
                    ["link", "image", "formula"],
                    ["clean"],
                ],
            },
            imageResize: {
                modules: ["Resize", "DisplaySize", "Toolbar"],
            },
        },
        theme: "snow",
    };

    function initializeQuillEditor(elementId, placeholder, hiddenInput) {
        const element = document.querySelector(elementId);
        if (!element) return null;

        const config = {
            ...quillConfigs,
            placeholder: placeholder || "Tulis teks...",
            formats: [
                "bold",
                "italic",
                "underline",
                "strike",
                "script",
                "formula",
                "image",
                "align",
                "link",
                "font",
                "header",
                "list",
                "indent",
                "direction",
            ],
        };

        function cleanupFormula(input) {
            return input.replace(/(\$[^$]+\$)\1+/g, "$1");
        }

        const quill = new Quill(element, config);

        quill.on("text-change", function () {
            let content = quill.root.innerHTML.trim();
            content = cleanupFormula(content);

            if (hiddenInput) {
                hiddenInput.value = content;
            }

            // Remove validation error when user starts typing
            const errorMessage = hiddenInput.nextElementSibling;
            if (errorMessage) {
                errorMessage.style.display =
                    content.length === 0 ? "block" : "none";
            }
        });

        return quill;
    }

    function createQuestionHTML(questionIndex) {
        return `
            <div class="question-block border border-gray-200 rounded-xl p-6 space-y-6" data-question-index="${questionIndex}">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-medium text-gray-900">Pertanyaan ${
                        questionIndex + 1
                    }</h4>
                    <button type="button" class="remove-question text-gray-400 hover:text-red-500">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="rounded-md">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
                    <div id="quill-editor-${questionIndex}" class="quill-editor"></div>
                    <input type="hidden" 
                        name="questions[${questionIndex}][content]" 
                        class="quill-content-input"
                        required>
                    <p class="quill-error text-red-500 text-sm mt-1" style="display: block;">Pertanyaan harus diisi.</p>
                </div>

                <div class="rounded-md mt-4">
                    <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                        <span class="text-gray-400">
                            <i data-lucide="list" class="w-5 h-5"></i>
                        </span>
                        <select name="questions[${questionIndex}][question_type]"
                            class="question-type-select block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            <option value="single_choice">Pilihan Ganda</option>
                            <option value="multiple_choice">Pilihan Ganda Kompleks</option>
                        </select>
                    </div>
                </div>

                <div class="options-section space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                        <button type="button" class="add-option px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                            Tambah Pilihan
                        </button>
                    </div>
                    <div class="options-container space-y-3">
                        ${createOptionHTML(questionIndex, 0, "radio")}
                    </div>
                </div>
            </div>
        `;
    }

    function createOptionHTML(questionIndex, optionIndex, type) {
        const optionId = `quill-option-${questionIndex}-${optionIndex}`;
        return `
            <div class="option-group" data-option-index="${optionIndex}">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div id="${optionId}" class="quill-option-editor"></div>
                        <input type="hidden" 
                            name="questions[${questionIndex}][options][]" 
                            class="quill-option-input"
                            required>
                        <p class="quill-error text-red-500 text-sm mt-1" style="display: block;">Jawaban harus diisi.</p>
                    </div>
                    <div class="flex items-center">
                        <input type="${type}" 
                            name="questions[${questionIndex}][correct_answer]${type === "checkbox" ? "[]" : ""}" 
                            value="${optionIndex}"
                            class="answer-input h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300"
                            ${type === "radio" ? "required" : ""}>
                        <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                    </div>
                    <button type="button" class="remove-option text-gray-400 hover:text-red-500">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Event Delegation
    questionsContainer.addEventListener("click", function (e) {
        const target = e.target.closest("button");
        if (!target) return;

        if (target.classList.contains("remove-question")) {
            handleRemoveQuestion(target);
        } else if (target.classList.contains("remove-option")) {
            handleRemoveOption(target);
        } else if (target.classList.contains("add-option")) {
            handleAddOption(target);
        }
    });

    questionsContainer.addEventListener("change", function (e) {
        if (e.target.classList.contains("question-type-select")) {
            handleQuestionTypeChange(e.target);
        }
    });

    function handleRemoveQuestion(button) {
        const questionBlock = button.closest(".question-block");
        if (questionsContainer.children.length > 1) {
            questionBlock.remove();
            renumberQuestions();
        } else {
            alert("Assessment harus memiliki minimal satu pertanyaan");
        }
    }

    function handleRemoveOption(button) {
        const optionGroup = button.closest(".option-group");
        const questionBlock = optionGroup.closest(".question-block");
        const optionsContainer =
            questionBlock.querySelector(".options-container");

        if (optionsContainer.children.length > 1) {
            optionGroup.remove();
            renumberOptions(questionBlock);
            updateCheckboxRequired(questionBlock);
        } else {
            alert("Pertanyaan harus memiliki minimal satu pilihan jawaban");
        }
    }

    function handleAddOption(button) {
        const questionBlock = button.closest(".question-block");
        const questionIndex = parseInt(questionBlock.dataset.questionIndex);
        const optionsContainer =
            questionBlock.querySelector(".options-container");
        const questionType = questionBlock.querySelector(
            ".question-type-select"
        ).value;
        const type = questionType === "single_choice" ? "radio" : "checkbox";
        const optionIndex = optionsContainer.children.length;

        optionsContainer.insertAdjacentHTML(
            "beforeend",
            createOptionHTML(questionIndex, optionIndex, type)
        );

        const optionId = `quill-option-${questionIndex}-${optionIndex}`;
        initializeQuillEditor(
            `#${optionId}`,
            "Masukkan pilihan jawaban...",
            optionsContainer.lastElementChild.querySelector(
                ".quill-option-input"
            )
        );

        lucide.createIcons();
    }

    function handleQuestionTypeChange(select) {
        const questionBlock = select.closest(".question-block");
        const type = select.value === "single_choice" ? "radio" : "checkbox";
        const questionIndex = parseInt(questionBlock.dataset.questionIndex);

        const inputs = questionBlock.querySelectorAll(".answer-input");
        inputs.forEach((input, index) => {
            input.type = type;
            input.name = `questions[${questionIndex}][correct_answer]${
                type === "checkbox" ? "[]" : ""
            }`;
            input.value = index;
            input.required = type === "radio";
        });

        if (type === "checkbox") {
            updateCheckboxRequired(questionBlock);
        }
    }

    // Add new question button handler
    addQuestionButton.addEventListener("click", function () {
        const newQuestion = createQuestionHTML(questionCount);
        questionsContainer.insertAdjacentHTML("beforeend", newQuestion);

        initializeQuillEditor(
            `#quill-editor-${questionCount}`,
            "Masukkan pertanyaan...",
            document.querySelector(
                `#quill-editor-${questionCount} + .quill-content-input`
            )
        );

        initializeQuillEditor(
            `#quill-option-${questionCount}-0`,
            "Masukkan pilihan jawaban...",
            document.querySelector(
                `#quill-option-${questionCount}-0 + .quill-option-input`
            )
        );

        questionCount++;
        lucide.createIcons();
    });

    // Initialize with first question
    addQuestionButton.click();
});
