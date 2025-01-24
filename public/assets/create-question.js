document.addEventListener("DOMContentLoaded", function () {
    const questionsContainer = document.getElementById("questions_container");
    const addQuestionButton = document.getElementById("add_question");
    let questionCount = 0;

    document.querySelectorAll(".question-block").forEach((questionBlock) => {
        const questionType = questionBlock.querySelector(
            'select[name*="question_type"]'
        ).value;
        if (questionType === "multiple_choice") {
            handleCheckboxGroup(questionBlock);
        }
    });

    function initialize() {
        initializeImageUpload();
    }

    // Image Upload Functionality
    function initializeImageUpload() {
        document.querySelectorAll(".border-dashed").forEach((uploadArea) => {
            uploadArea.addEventListener("dragenter", handleDragEnter);
            uploadArea.addEventListener("dragleave", handleDragLeave);
            uploadArea.addEventListener("dragover", handleDragOver);
            uploadArea.addEventListener("drop", handleDrop);
        });
    }

    function handleDragEnter(e) {
        e.preventDefault();
        e.stopPropagation();
        e.currentTarget.classList.add("border-orange-500", "bg-orange-50");
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        e.currentTarget.classList.remove("border-orange-500", "bg-orange-50");
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        const uploadArea = e.currentTarget;
        uploadArea.classList.remove("border-orange-500", "bg-orange-50");

        const fileInput = uploadArea.querySelector('input[type="file"]');
        const files = e.dataTransfer.files;

        if (files.length > 0) {
            // Get the question index from the parent structure
            const questionIndex = uploadArea
                .closest(".question-block")
                .querySelector('input[type="file"]')
                .getAttribute("name")
                .match(/questions\[(\d+)\]/)[1];

            // Validate first dropped file
            const file = files[0];
            const validTypes = ["image/jpeg", "image/png", "image/gif"];

            if (!validTypes.includes(file.type)) {
                alert("Mohon upload file gambar (PNG, JPG, atau GIF)");
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("Ukuran file tidak boleh lebih dari 2MB");
                return;
            }

            // Set files and trigger upload
            fileInput.files = files;
            handleImageUpload(fileInput);
        }
    }

    function validateImageFile(file) {
        const validTypes = ["image/jpeg", "image/png", "image/gif"];
        const maxFileSize = 2 * 1024 * 1024; // 2MB

        if (!file) {
            console.error("No file selected");
            return false;
        }

        if (!validTypes.includes(file.type)) {
            alert("Hanya file gambar (PNG, JPG, atau GIF) yang diperbolehkan");
            return false;
        }

        if (file.size > maxFileSize) {
            alert("Ukuran file tidak boleh lebih dari 2MB");
            return false;
        }

        return true;
    }

    // Image Processing Functions
    window.handleImageUpload = function (input) {
        const file = input.files[0];
        if (!file) return;

        if (!validateImageFile(file)) {
            input.value = ""; // Clear the input
            return;
        }    

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("Ukuran file tidak boleh lebih dari 2MB");
            input.value = "";
            return;
        }

        // Safe extraction of question index
        const match = input.name.match(/questions\[(\d+)\]/);
        if (!match) {
            console.error("Invalid input name format");
            return;
        }

        const questionIndex = match[1];
        previewImage(input, questionIndex);
    };

    window.previewImage = function (input, questionIndex) {
        const uploadContainer = input.closest(".border-dashed");
        const previewContainer = document.getElementById(
            `image-preview-${questionIndex}`
        );
        const file = input.files[0];

        const reader = new FileReader();
        reader.onload = function (e) {
            previewContainer.classList.remove("hidden");
            previewContainer.querySelector("img").src = e.target.result;
            uploadContainer.classList.add("hidden");
        };

        reader.onerror = function () {
            alert("Gagal membaca file gambar");
            input.value = "";
        };

        reader.readAsDataURL(file);
    };

    window.clearImage = function (button, questionIndex) {
        const previewContainer = document.getElementById(
            `image-preview-${questionIndex}`
        );
        const uploadContainer = previewContainer.nextElementSibling;
        previewContainer.classList.add("hidden");
        previewContainer.querySelector("img").src = "";
        const input =
            previewContainer.nextElementSibling.querySelector(
                'input[type="file"]'
            );
        input.value = "";
        uploadContainer.classList.remove("hidden");
    };

    function createQuestionBlock(questionIndex) {
        const questionBlock = document.createElement("div");
        questionBlock.className =
            "question-block border border-gray-200 rounded-xl p-6 space-y-6";
        questionBlock.dataset.questionIndex = questionIndex;

        questionBlock.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pertanyaan ${
                    questionIndex + 1
                }</h3>
                <button type="button" onclick="removeQuestion(this)" 
                    class="text-gray-400 hover:text-red-500">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Question Text -->
            <div class="rounded-md">
                <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                    <span class="text-gray-400">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                    </span>
                    <input type="text" required name="questions[${questionIndex}][question_text]"
                        placeholder="Masukkan pertanyaan"
                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                </div>
            </div>

             <!-- Image Upload -->
        <div class="rounded-md">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Pertanyaan (Opsional)</label>
            <div class="relative">
                <!-- Image Preview Container -->
                <div id="image-preview-${questionIndex}" class="hidden mb-3">
                    <div class="relative inline-block group">
                        <img src="" alt="Preview" class="max-h-48 rounded-lg border border-gray-200">
                        <button type="button" 
                            onclick="clearImage(this, ${questionIndex})"
                            class="absolute -top-2 -right-2 bg-white rounded-full p-1.5 shadow-md border border-gray-200 text-gray-400 hover:text-red-500 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- File Upload Area -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 transition-all duration-200 ease-in-out">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <span class="text-gray-400">
                            <i data-lucide="image-plus" class="w-10 h-10"></i>
                        </span>
                        <div class="text-center space-y-2">
                            <label class="relative cursor-pointer">
                                <span class="text-orange-600 hover:text-orange-700 text-sm font-medium">Klik untuk upload</span>
                                <span class="text-gray-500 text-sm"> atau drag and drop</span>
                                <input type="file" 
                                    name="questions[${questionIndex}][image]"
                                    accept="image/*"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    onchange="handleImageUpload(this)">
                            </label>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF (Maks. 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Question Type -->
            <div class="rounded-md">
                <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                    <span class="text-gray-400">
                        <i data-lucide="list" class="w-5 h-5"></i>
                    </span>
                    <select name="questions[${questionCount}][question_type]"
                        onchange="updateQuestionType(this, ${questionCount})"
                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                        <option value="single_choice">Pilihan Ganda</option>
                        <option value="multiple_choice">Pilihan Ganda Kompleks</option>
                    </select>
                </div>
            </div>

            <!-- Options Section -->
            <div class="options-section space-y-4">
                <div class="flex justify-between items-center">
                    <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                    <button type="button" onclick="addOption(this, ${questionCount})"
                        class="px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                        Tambah Pilihan
                    </button>
                </div>
                <div class="options-container space-y-3">
                    ${createOptionHTML(questionCount, 0, "radio")}
                </div>
            </div>
        `;

        return questionBlock;
    }

    addQuestionButton.addEventListener("click", function () {
        const newQuestion = createQuestionBlock(questionCount);
        questionsContainer.appendChild(newQuestion);
        questionCount++;
        lucide.createIcons();
        renumberQuestions();
    });

    // Add first question automatically
    addQuestionButton.click();

    initialize();
});

function handleCheckboxGroup(questionBlock) {
    const checkboxes = questionBlock.querySelectorAll(
        'input[type="checkbox"][name$="[correct_answer][]"]'
    );

    // Fungsi untuk update required state
    function updateRequiredState() {
        const isAnyChecked = Array.from(checkboxes).some((cb) => cb.checked);
        checkboxes.forEach((cb) => {
            cb.required = !isAnyChecked;
        });
    }

    // Tambahkan event listener untuk setiap checkbox
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", updateRequiredState);
    });

    // Set initial state
    updateRequiredState();
}

// Update fungsi createOptionHTML untuk multiple choice
function createOptionHTML(questionIndex, optionIndex, type) {
    const html = `
<div class="option-group">
    <div class="flex items-center gap-3">
        <div class="flex-1">
            <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                <span class="text-gray-400">
                    <i data-lucide="circle" class="w-5 h-5"></i>
                </span>
                <input type="text" required name="questions[${questionIndex}][options][]" 
                    placeholder="Masukkan pilihan jawaban"
                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
            </div>
        </div>
        <div class="flex items-center">
            <input type="${type}" 
                name="questions[${questionIndex}][correct_answer]${
        type === "checkbox" ? "[]" : ""
    }" 
                value="${optionIndex}"
                ${type === "radio" ? "required" : ""}
                class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
            <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
        </div>
        <button type="button" onclick="removeOption(this)" 
            class="text-gray-400 hover:text-red-500">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
        </button>
    </div>
</div>
`;
    return html;
}

function handleCheckboxRequired(checkbox) {
    const questionBlock = checkbox.closest(".question-block");
    const checkboxes = questionBlock.querySelectorAll(
        'input[type="checkbox"][name="' + checkbox.getAttribute("name") + '"]'
    );

    // Check if any checkbox is checked
    const isAnyChecked = Array.from(checkboxes).some((cb) => cb.checked);

    // Update required attribute for all checkboxes in the group
    checkboxes.forEach((cb) => {
        cb.required = !isAnyChecked;
    });
}

function addOption(button, questionIndex) {
    const questionBlock = button.closest(".question-block");
    const optionsContainer = questionBlock.querySelector(".options-container");
    const questionType = questionBlock.querySelector(
        'select[name*="question_type"]'
    ).value;
    const type = questionType === "single_choice" ? "radio" : "checkbox";
    const optionIndex = optionsContainer.children.length;

    const newOption = createOptionHTML(questionIndex, optionIndex, type);
    optionsContainer.insertAdjacentHTML("beforeend", newOption);

    if (type === "checkbox") {
        handleCheckboxGroup(questionBlock);
    }

    lucide.createIcons();
}

function removeOption(button) {
    const optionGroup = button.closest(".option-group");
    const optionsContainer = optionGroup.closest(".options-container");

    if (optionsContainer.children.length > 1) {
        optionGroup.remove();
        updateOptionIndexes(optionsContainer);
    } else {
        alert("Pertanyaan harus memiliki minimal satu pilihan jawaban");
    }
}

function removeQuestion(button) {
    const questionBlock = button.closest(".question-block");
    const questionsContainer = questionBlock.parentElement;

    if (questionsContainer.children.length > 1) {
        questionBlock.remove();
        renumberQuestions();
    } else {
        alert("Assessment harus memiliki minimal satu pertanyaan");
    }
}

function updateQuestionType(select, questionIndex) {
    const questionBlock = select.closest(".question-block");
    const optionsContainer = questionBlock.querySelector(".options-container");
    const type = select.value === "single_choice" ? "radio" : "checkbox";

    // Simpan status checked dari input sebelumnya
    const previousValues = Array.from(
        optionsContainer.querySelectorAll(
            'input[type="radio"], input[type="checkbox"]'
        )
    ).map((input) => input.checked);

    // Buat ulang semua option dengan tipe yang baru
    const optionGroups = optionsContainer.querySelectorAll(".option-group");
    optionGroups.forEach((group, index) => {
        const optionText = group.querySelector('input[type="text"]').value;
        const newOptionHTML = createOptionHTML(questionIndex, index, type);
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = newOptionHTML;
        const newOptionGroup = tempDiv.firstElementChild;

        // Salin nilai teks dan checked status
        newOptionGroup.querySelector('input[type="text"]').value = optionText;
        const newInput = newOptionGroup.querySelector(`input[type="${type}"]`);
        newInput.checked = previousValues[index];

        group.replaceWith(newOptionGroup);
    });

    // Initialize checkbox handling if needed
    if (type === "checkbox") {
        handleCheckboxGroup(questionBlock);
    }

    lucide.createIcons();
}

function renumberQuestions() {
    const questions = document.querySelectorAll(".question-block");
    questions.forEach((question, index) => {
        const heading = question.querySelector("h3");
        heading.textContent = `Pertanyaan ${index + 1}`;

        // Update all input names with new index
        updateQuestionInputs(question, index);
    });
}

function updateQuestionInputs(questionBlock, newIndex) {
    // Update question text input
    const questionText = questionBlock.querySelector(
        'input[name*="[question_text]"]'
    );
    questionText.name = `questions[${newIndex}][question_text]`;

    // Update question type select
    const questionType = questionBlock.querySelector(
        'select[name*="[question_type]"]'
    );
    questionType.name = `questions[${newIndex}][question_type]`;
    questionType.setAttribute(
        "onchange",
        `updateQuestionType(this, ${newIndex})`
    );

    // Update option inputs
    const optionsContainer = questionBlock.querySelector(".options-container");
    updateOptionIndexes(optionsContainer, newIndex);

    // Update add option button
    const addOptionBtn = questionBlock.querySelector(".options-section button");
    addOptionBtn.setAttribute("onclick", `addOption(this, ${newIndex})`);
}

function updateOptionIndexes(optionsContainer, questionIndex) {
    const options = optionsContainer.querySelectorAll(".option-group");
    const type = optionsContainer.querySelector(
        'input[type="radio"], input[type="checkbox"]'
    ).type;

    options.forEach((option, index) => {
        const optionInput = option.querySelector('input[name*="[options]"]');
        const correctInput = option.querySelector(`input[type="${type}"]`);

        if (questionIndex !== undefined) {
            optionInput.name = `questions[${questionIndex}][options][]`;
            correctInput.name = `questions[${questionIndex}][correct_answer]${
                type === "checkbox" ? "[]" : ""
            }`;
        }
        correctInput.value = index;
    });
}
