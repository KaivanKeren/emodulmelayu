document.addEventListener("DOMContentLoaded", function() {
    const questionsContainer = document.getElementById("questions_container");
    const addQuestionButton = document.getElementById("add_question");
    let questionCount = 0;

    // Initialize the form
    function initialize() {
        initializeImageUpload();
        // Add first question automatically
        addQuestionButton.click();
    }

    // Image Upload Functionality
    function initializeImageUpload() {
        document.querySelectorAll(".border-dashed").forEach(uploadArea => {
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

        if (files.length) {
            fileInput.files = files;
            handleImageUpload(fileInput);
        }
    }

    // Image Processing Functions
    window.handleImageUpload = function(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file type
        const validTypes = ["image/jpeg", "image/png", "image/gif"];
        if (!validTypes.includes(file.type)) {
            alert("Mohon upload file gambar (PNG, JPG, atau GIF)");
            input.value = "";
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("Ukuran file tidak boleh lebih dari 2MB");
            input.value = "";
            return;
        }

        // Get question index from the input name
        const questionIndex = input.name.match(/questions\[(\d+)\]/)[1];
        previewImage(input, questionIndex);
    };

    window.previewImage = function(input, questionIndex) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        const previewContainer = document.getElementById(`image-preview-${questionIndex}`);
        const uploadArea = input.closest(".border-dashed");
        const previewImg = previewContainer.querySelector("img");

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.classList.remove("hidden");
            uploadArea.classList.add("hidden");
        };

        reader.readAsDataURL(file);
    };

    window.clearImage = function(button, questionIndex) {
        const previewContainer = document.getElementById(`image-preview-${questionIndex}`);
        const questionBlock = button.closest(".question-block");
        const uploadArea = questionBlock.querySelector(".border-dashed");
        const fileInput = questionBlock.querySelector('input[type="file"]');

        fileInput.value = "";
        previewContainer.classList.add("hidden");
        previewContainer.querySelector("img").src = "";
        uploadArea.classList.remove("hidden");
    };

    // Question Management Functions
    function createQuestionHTML(questionIndex) {
        return `
            <div class="question-block border border-gray-200 rounded-xl p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-medium text-gray-900">Pertanyaan ${questionIndex + 1}</h4>
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
                            class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300"
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

    // Global Functions
    window.removeQuestion = function(button) {
        button.closest(".question-block").remove();
    };

    window.removeOption = function(button) {
        button.closest(".option-group").remove();
    };

    window.addOption = function(button, questionIndex) {
        const optionsContainer = button.closest(".options-section").querySelector(".options-container");
        const questionType = button.closest(".question-block").querySelector('select[name*="question_type"]').value;
        const type = questionType === "single_choice" ? "radio" : "checkbox";
        const optionIndex = optionsContainer.children.length;

        optionsContainer.insertAdjacentHTML("beforeend", createOptionHTML(questionIndex, optionIndex, type));
        lucide.createIcons();
    };

    window.updateQuestionType = function(select, questionIndex) {
        const optionsContainer = select.closest(".question-block").querySelector(".options-container");
        const type = select.value === "single_choice" ? "radio" : "checkbox";

        const inputs = optionsContainer.querySelectorAll('input[type="radio"], input[type="checkbox"]');
        inputs.forEach((input, index) => {
            input.type = type;
            input.name = `questions[${questionIndex}][correct_answer]${type === "checkbox" ? "[]" : ""}`;
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

    function updateCheckboxRequired(checkbox) {
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

    // Event Listeners
    addQuestionButton.addEventListener("click", function() {
        const newQuestion = createQuestionHTML(questionCount);
        questionsContainer.insertAdjacentHTML("beforeend", newQuestion);
        questionCount++;
        lucide.createIcons();
        initializeImageUpload();
    });

    // Initialize the form
    initialize();
});