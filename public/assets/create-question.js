document.addEventListener("DOMContentLoaded", function () {
    // Register FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginFileValidateType,
        FilePondPluginImageCrop
    );

    const questionsContainer = document.getElementById("questions_container");
    const addQuestionButton = document.getElementById("add_question");
    const form = document.querySelector("form"); // Get the form element
    let questionCount = 0;

    function createOptionHTML(questionIndex, optionIndex, type) {
        return `
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
                    name="questions[${questionIndex}][correct_answer]${type === "checkbox" ? "[]" : ""}" 
                    value="${optionIndex}"
                    class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 answer-checkbox"
                    onchange="updateCheckboxRequired(${questionIndex})"
                    ${type === "radio" ? "required" : ""}>
                <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
            </div>
            <button type="button" onclick="removeOption(this)" 
                class="text-gray-400 hover:text-red-500">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
`;
    }

    function createQuestionHTML(questionIndex) {
        return `
    <div class="question-block border border-gray-200 rounded-xl p-6 space-y-6" data-question-index="${questionIndex}">
        <div class="flex justify-between items-center">
            <h4 class="text-lg font-medium text-gray-900">Pertanyaan ${
                questionIndex + 1
            }</h4>
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
                <input type="text" required name="questions[${questionIndex}][content]"
                    placeholder="Masukkan pertanyaan"
                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
            </div>
        </div>

        <!-- Image Upload -->
        <div class="rounded-md">
            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Pertanyaan (Opsional)</label>
            <input type="file" 
                class="filepond question-image-input"
                name="questions[${questionIndex}][image]"
                accept="image/jpeg,image/png,image/jpg,image/gif">
        </div>

        <!-- Question Type -->
        <div class="rounded-md">
            <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                <span class="text-gray-400">
                    <i data-lucide="list" class="w-5 h-5"></i>
                </span>
                <select name="questions[${questionIndex}][question_type]"
                    onchange="updateQuestionType(this, ${questionIndex})"
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
                <button type="button" onclick="addOption(this, ${questionIndex})"
                    class="px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
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

    // Initialize FilePond for a question
    function initializeFilePond(questionBlock) {
        const inputElement = questionBlock.querySelector(
            ".question-image-input"
        );
        if (!inputElement) return;

        const pond = FilePond.create(inputElement, {
            imagePreviewHeight: 200,
            imageCropAspectRatio: "16:9",
            imageResizeTargetWidth: 600,
            imageResizeTargetHeight: 337,
            acceptedFileTypes: [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
            ],
            credits: false,
            allowImagePreview: true,
            imagePreviewMaxHeight: 256,
            instantUpload: false,
            allowMultiple: false,
            allowReplace: true,
            allowImageCrop: true,
            allowImageResize: true,
            imageResizeMode: "contain",
            labelFileTypeNotAllowed: "File harus berupa gambar (JPG, PNG, GIF)",
            fileValidateTypeLabelExpectedTypes:
                "Format yang diperbolehkan: {allTypes}",
            maxFileSize: "2MB",
            labelMaxFileSizeExceeded: "File terlalu besar, maksimal 2MB",
            onwarning: (error) => {
                console.log("FilePond warning:", error);
                showError("Warning: " + error.body);
            },
            onerror: (error) => {
                console.log("FilePond error:", error);
                showError("Error: " + error.body);
            },
        });

        inputElement.pondInstance = pond;
    }

    function showError(message) {
        const errorDiv = document.createElement("div");
        errorDiv.className =
            "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4";
        errorDiv.role = "alert";
        errorDiv.innerHTML = `
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">${message}</span>
        `;
        form.insertBefore(errorDiv, form.firstChild);

        // Remove error after 5 seconds
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // Form submission handler
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Create FormData object
        const formData = new FormData(form);

        // Handle FilePond images
        const questionBlocks = document.querySelectorAll(".question-block");
        for (let i = 0; i < questionBlocks.length; i++) {
            const pond = questionBlocks[i].querySelector(
                ".question-image-input"
            ).pondInstance;
            if (pond && pond.getFiles().length > 0) {
                const file = pond.getFiles()[0].file;
                formData.set(`questions[${i}][image]`, file);
                console.log(`Question ${i} image:`, file); // Check if file exists
            }
        }

        for (let pair of formData.entries()) {
            console.log(pair[0], pair[1]);
        }

        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            ).content;

            // Send the form data
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: formData,
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(
                    errorData.message || "Terjadi kesalahan saat menyimpan data"
                );
            }

            const result = await response.json();
            window.location.href = "/assessments"; // Redirect to index page on success
        } catch (error) {
            console.error("Error:", error);
            // Show error message to user
            alert(error.message || "Terjadi kesalahan saat menyimpan data");
        }
    });

    addQuestionButton.addEventListener("click", function () {
        const newQuestion = createQuestionHTML(questionCount);
        questionsContainer.insertAdjacentHTML("beforeend", newQuestion);
        const questionBlock = questionsContainer.lastElementChild;
        initializeFilePond(questionBlock);
        questionCount++;
        lucide.createIcons();
    });

    window.removeQuestion = function (button) {
        const questionBlock = button.closest(".question-block");
        const pondInput = questionBlock.querySelector(".question-image-input");

        if (pondInput && pondInput.pondInstance) {
            pondInput.pondInstance.destroy();
        }

        questionBlock.remove();
    };

    window.removeOption = function (button) {
        const optionGroup = button.closest(".option-group");
        const questionBlock = button.closest(".question-block");
        const questionIndex = questionBlock.dataset.questionIndex;

        optionGroup.remove();

        // Update remaining option values
        const options = questionBlock.querySelectorAll(".answer-checkbox");
        options.forEach((option, index) => {
            option.value = index;
        });
    };

    window.addOption = function (button, questionIndex) {
        const optionsContainer = button
            .closest(".options-section")
            .querySelector(".options-container");
        const questionType = button
            .closest(".question-block")
            .querySelector('select[name*="question_type"]').value;
        const type = questionType === "single_choice" ? "radio" : "checkbox";
        const optionIndex = optionsContainer.children.length;

        const newOption = createOptionHTML(questionIndex, optionIndex, type);
        optionsContainer.insertAdjacentHTML("beforeend", newOption);
        lucide.createIcons();
    };

    window.updateQuestionType = function (select, questionIndex) {
        const optionsContainer = select
            .closest(".question-block")
            .querySelector(".options-container");
        const type = select.value === "single_choice" ? "radio" : "checkbox";

        const inputs = optionsContainer.querySelectorAll(
            'input[type="radio"], input[type="checkbox"]'
        );
        inputs.forEach((input, index) => {
            input.type = type;
            input.name = `questions[${questionIndex}][correct_answer]${
                type === "checkbox" ? "[]" : ""
            }`;
            input.value = index;
            input.required = type === "radio";
            if (type === "checkbox") {
                input.onchange = () => updateCheckboxRequired(questionIndex);
            }
        });

        if (type === "checkbox") {
            updateCheckboxRequired(questionIndex);
        }
    };

    window.updateCheckboxRequired = function (questionIndex) {
        const questionBlock = document.querySelector(
            `.question-block:nth-child(${questionIndex + 1})`
        );
        const checkboxes = questionBlock.querySelectorAll(
            'input[type="checkbox"]'
        );

        let isAnyChecked = false;
        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                isAnyChecked = true;
            }
        });

        checkboxes.forEach((checkbox) => {
            checkbox.required = !isAnyChecked;
        });
    };

    // Add first question automatically
    addQuestionButton.click();
});
