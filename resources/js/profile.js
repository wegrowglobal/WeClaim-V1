class Profile {
    constructor() {
        this.profilePictureInput = document.getElementById('profile_picture');
        this.profilePictureContainer = document.querySelector('.profile-picture');
        this.form = document.querySelector('form');
        
        if (this.profilePictureInput && this.profilePictureContainer) {
            this.initializeUpload();
            this.initializeBankingToggles();
        }

        // Initialize success message if it exists
        this.showSuccessMessage();

        // Add click handler for change password button
        const changePasswordBtn = document.querySelector('[onclick="Profile.showChangePasswordModal()"]');
        if (changePasswordBtn) {
            changePasswordBtn.onclick = () => this.showChangePasswordModal();
        }
    }

    showSuccessMessage() {
        const successMessage = document.querySelector('[data-success-message]');
        if (successMessage) {
            const message = successMessage.getAttribute('data-success-message');
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                timer: 3000,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        }
    }

    initializeUpload() {
        this.profilePictureInput.addEventListener('change', (e) => {
            this.handleFileSelect(e);
        });

        this.initializeDragAndDrop();
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file && this.validateFile(file)) {
            this.displayPreview(file);
        }
    }

    validateFile(file) {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!validTypes.includes(file.type)) {
            Swal.fire({
                title: 'Invalid File Type',
                text: 'Please upload an image file (JPG, PNG, or GIF)',
                icon: 'error',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            return false;
        }

        if (file.size > maxSize) {
            Swal.fire({
                title: 'File Too Large',
                text: 'File size must be less than 2MB',
                icon: 'error',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            return false;
        }

        return true;
    }

    displayPreview(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            // Find the existing profile picture component
            const profilePictureComponent = this.profilePictureContainer.querySelector('div');
            if (profilePictureComponent) {
                let img = profilePictureComponent.querySelector('img');
                if (!img) {
                    // Remove only the default avatar div if it exists
                    const defaultAvatar = profilePictureComponent.querySelector('.rounded-full:not(img)');
                    if (defaultAvatar) {
                        defaultAvatar.remove();
                    }
                    
                    // Create new image element
                    img = document.createElement('img');
                    img.classList.add('w-full', 'h-full', 'object-cover', 'rounded-full');
                    profilePictureComponent.insertBefore(img, profilePictureComponent.firstChild);
                }
                
                // Update image attributes
                img.src = e.target.result;
                img.alt = 'Profile preview';
            }
        };
        
        reader.readAsDataURL(file);
    }

    initializeDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.profilePictureContainer.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.profilePictureContainer.addEventListener(eventName, () => {
                this.profilePictureContainer.classList.add('ring-indigo-400');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.profilePictureContainer.addEventListener(eventName, () => {
                this.profilePictureContainer.classList.remove('ring-indigo-400');
            });
        });

        this.profilePictureContainer.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && this.validateFile(file)) {
                this.profilePictureInput.files = e.dataTransfer.files;
                this.displayPreview(file);
            }
        });
    }

    // Banking information visibility toggles
    initializeBankingToggles() {
        const toggleVisibility = (inputId) => {
            const input = document.getElementById(inputId);
            const showIcon = document.getElementById(`show${inputId.charAt(0).toUpperCase() + inputId.slice(1)}Icon`);
            const hideIcon = document.getElementById(`hide${inputId.charAt(0).toUpperCase() + inputId.slice(1)}Icon`);

            if (input.type === 'password') {
                input.type = 'text';
                showIcon.classList.add('hidden');
                hideIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
            }
        };

        // Add click listeners to toggle buttons
        document.querySelectorAll('[data-toggle-visibility]').forEach(button => {
            button.addEventListener('click', () => {
                const inputId = button.getAttribute('data-toggle-visibility');
                toggleVisibility(inputId);
            });
        });
    }

    async showChangePasswordModal() {
        const { value: formValues } = await Swal.fire({
            title: 'Change Password',
            html: `
                <div class="space-y-4">
                    <div class="relative">
                        <input type="password" 
                               id="current_password" 
                               class="block w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all sm:text-sm" 
                               placeholder="Current Password">
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="Profile.togglePasswordVisibility('current_password')">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               id="new_password" 
                               class="block w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all sm:text-sm" 
                               placeholder="New Password"
                               oninput="window.profileInstance.updatePasswordStrength()">
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            <button type="button"
                                    class="px-3 py-1 mr-8 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all"
                                    onclick="const profile = new Profile(); profile.fillPasswordFields(profile.generatePassword())">
                                Generate
                            </button>
                            <button type="button" 
                                    class="pr-3 flex items-center"
                                    onclick="Profile.togglePasswordVisibility('new_password')">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               id="new_password_confirmation" 
                               class="block w-full px-4 py-3 rounded-lg border bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all sm:text-sm" 
                               placeholder="Confirm New Password">
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="Profile.togglePasswordVisibility('new_password_confirmation')">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-500">Password Strength:</span>
                            <span id="password-strength-text" class="text-xs text-gray-500">No Password</span>
                        </div>
                        <div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
                            <div id="password-strength-bar" class="h-full bg-gray-300 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 text-left rounded-lg text-sm text-blue-600">
                        <h4 class="font-semibold mb-2">Password Requirements:</h4>
                        <ul class="list-disc list-inside">
                            <li>At least 8 characters long</li>
                            <li>Include at least one uppercase letter</li>
                            <li>Include at least one lowercase letter</li>
                            <li>Include at least one number</li>
                            <li>Include at least one special character (!@#$%^&*)</li>
                        </ul>
                        <p class="mt-2">For a stronger password, consider using a passphrase or a combination of unrelated words.</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Change Password',
            confirmButtonColor: '#4F46E5',
            cancelButtonText: 'Cancel',
            customClass: {
                input: 'form-input-base peer'
            },
            focusConfirm: false,
            preConfirm: () => {
                const current_password = document.getElementById('current_password').value;
                const password = document.getElementById('new_password').value;
                const password_confirmation = document.getElementById('new_password_confirmation').value;

                if (!current_password || !password || !password_confirmation) {
                    Swal.showValidationMessage('All fields are required');
                    return false;
                }

                if (password.length < 8) {
                    Swal.showValidationMessage('Password must be at least 8 characters long');
                    return false;
                }

                if (password !== password_confirmation) {
                    Swal.showValidationMessage('Passwords do not match');
                    return false;
                }

                return { current_password, password, password_confirmation };
            }
        });

        if (formValues) {
            try {
                const response = await fetch('/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formValues)
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Password has been updated successfully',
                        icon: 'success',
                        timer: 3000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                } else {
                    throw new Error(data.message || 'Failed to update password');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }
        }
    }

    static togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    generatePassword() {
        const length = 16;
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*';
        const all = uppercase + lowercase + numbers + symbols;
        
        let password = '';
        // Ensure at least one of each required character type
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += symbols.charAt(Math.floor(Math.random() * symbols.length));
        
        // Fill the rest with random characters
        for (let i = password.length; i < length; i++) {
            password += all.charAt(Math.floor(Math.random() * all.length));
        }
        
        // Shuffle the password
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        return password;
    }

    fillPasswordFields(password) {
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');
        if (newPasswordInput && confirmPasswordInput) {
            newPasswordInput.value = password;
            confirmPasswordInput.value = password;
        }
    }

    calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 20;
        if (password.match(/[A-Z]/)) strength += 20;
        if (password.match(/[a-z]/)) strength += 20;
        if (password.match(/[0-9]/)) strength += 20;
        if (password.match(/[^A-Za-z0-9]/)) strength += 20;
        
        return strength;
    }

    updatePasswordStrength() {
        const password = document.getElementById('new_password').value;
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        
        const strength = this.calculatePasswordStrength(password);
        
        // Update the bar width and color
        strengthBar.style.width = `${strength}%`;
        
        // Update color based on strength
        if (strength <= 20) {
            strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
            strengthText.textContent = 'Very Weak';
            strengthText.className = 'text-xs text-red-500';
        } else if (strength <= 40) {
            strengthBar.className = 'h-full bg-orange-500 transition-all duration-300';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-xs text-orange-500';
        } else if (strength <= 60) {
            strengthBar.className = 'h-full bg-yellow-500 transition-all duration-300';
            strengthText.textContent = 'Medium';
            strengthText.className = 'text-xs text-yellow-500';
        } else if (strength <= 80) {
            strengthBar.className = 'h-full bg-blue-500 transition-all duration-300';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-xs text-blue-500';
        } else {
            strengthBar.className = 'h-full bg-green-500 transition-all duration-300';
            strengthText.textContent = 'Very Strong';
            strengthText.className = 'text-xs text-green-500';
        }
    }
}

// Make Profile globally accessible
window.Profile = Profile;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.profileInstance = new Profile();
}); 