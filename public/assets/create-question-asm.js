document.addEventListener("DOMContentLoaded", function () {
    const questionsContainer = document.getElementById("questions_container");
    const addQuestionButton = document.getElementById("add_question");
    let questionCount = 0;

    // Enhanced Quill configuration with image handler
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

    // Initialize Quill Editor with proper configuration
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

    // Event delegation for dynamic elements
    questionsContainer.addEventListener("click", function (e) {
        const target = e.target.closest("button");
        if (!target) return;

        if (target.classList.contains("remove-question")) {
            const questionBlock = target.closest(".question-block");
            if (questionBlock) {
                questionBlock.remove();
                renumberQuestions();
            }
        } else if (target.classList.contains("remove-option")) {
            const optionGroup = target.closest(".option-group");
            const questionBlock = optionGroup.closest(".question-block");
            if (optionGroup && questionBlock) {
                optionGroup.remove();
                renumberOptions(questionBlock);
                updateCheckboxRequired(questionBlock);
            }
        } else if (target.classList.contains("add-option")) {
            const questionBlock = target.closest(".question-block");
            const questionIndex = parseInt(questionBlock.dataset.questionIndex);
            const optionsContainer =
                questionBlock.querySelector(".options-container");
            const questionType = questionBlock.querySelector(
                ".question-type-select"
            ).value;
            const type =
                questionType === "single_choice" ? "radio" : "checkbox";
            const optionIndex = optionsContainer.children.length;

            optionsContainer.insertAdjacentHTML(
                "beforeend",
                createOptionHTML(questionIndex, optionIndex, type)
            );

            const optionId = `quill-option-${questionIndex}-${optionIndex}`;
            const optionInput = document.querySelector(
                `#${optionId} + .quill-option-input`
            );

            initializeQuillEditor(
                `#${optionId}`,
                "Masukkan pilihan jawaban...",
                optionInput
            );

            lucide.createIcons();
        }
    });

    // Event delegation for select changes
    questionsContainer.addEventListener("change", function (e) {
        if (e.target.classList.contains("question-type-select")) {
            const questionBlock = e.target.closest(".question-block");
            const type =
                e.target.value === "single_choice" ? "radio" : "checkbox";
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
    });

    function updateCheckboxRequired(questionBlock) {
        const checkboxes = questionBlock.querySelectorAll(
            'input[type="checkbox"]'
        );
        const isAnyChecked = Array.from(checkboxes).some((cb) => cb.checked);
        checkboxes.forEach((cb) => {
            if (isAnyChecked) {
                cb.removeAttribute("required");
            } else {
                cb.setAttribute("required", "required");
            }
        });
    }

    function renumberQuestions() {
        document.querySelectorAll(".question-block").forEach((block, index) => {
            block.dataset.questionIndex = index;
            const header = block.querySelector("h4");
            if (header) {
                header.textContent = `Pertanyaan ${index + 1}`;
            }

            // Update all name attributes within the question block
            updateQuestionBlockIndexes(block, index);
        });
    }

    function updateQuestionBlockIndexes(block, newIndex) {
        // Update question content input
        const contentInput = block.querySelector(".quill-content-input");
        if (contentInput) {
            contentInput.name = `questions[${newIndex}][content]`;
        }

        // Update question type select
        const typeSelect = block.querySelector(".question-type-select");
        if (typeSelect) {
            typeSelect.name = `questions[${newIndex}][question_type]`;
        }

        // Update options
        const optionInputs = block.querySelectorAll(".quill-option-input");
        optionInputs.forEach((input, i) => {
            input.name = `questions[${newIndex}][options][]`;
        });

        // Update answer inputs
        const answerInputs = block.querySelectorAll(".answer-input");
        const type =
            typeSelect.value === "single_choice" ? "radio" : "checkbox";
        answerInputs.forEach((input, i) => {
            input.name = `questions[${newIndex}][correct_answer]${
                type === "checkbox" ? "[]" : ""
            }`;
            input.value = i;
        });
    }

    questionsContainer.addEventListener("change", function (e) {
        if (e.target.type === "checkbox") {
            const questionBlock = e.target.closest(".question-block");
            if (questionBlock) {
                updateCheckboxRequired(questionBlock);
            }
        }
    });

    function renumberOptions(questionBlock) {
        const options = questionBlock.querySelectorAll(".option-group");
        options.forEach((option, index) => {
            option.dataset.optionIndex = index;
            const input = option.querySelector(".answer-input");
            if (input) {
                input.value = index;
            }
        });
    }

    // Add new question button handler
    addQuestionButton.addEventListener("click", function () {
        const newQuestion = createQuestionHTML(questionCount);
        questionsContainer.insertAdjacentHTML("beforeend", newQuestion);

        // Initialize main question editor
        const contentInput = document.querySelector(
            `#quill-editor-${questionCount} + .quill-content-input`
        );
        initializeQuillEditor(
            `#quill-editor-${questionCount}`,
            "Masukkan pertanyaan...",
            contentInput
        );

        // Initialize first option editor
        const optionInput = document.querySelector(
            `#quill-option-${questionCount}-0 + .quill-option-input`
        );
        initializeQuillEditor(
            `#quill-option-${questionCount}-0`,
            "Masukkan pilihan jawaban...",
            optionInput
        );

        questionCount++;
        lucide.createIcons();
    });

    // Initialize the form with first question
    addQuestionButton.click();
});
