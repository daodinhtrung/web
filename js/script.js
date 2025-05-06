document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo Swiper nếu có
    const swiperContainer = document.querySelector('.swiper-container');
    if (swiperContainer && typeof Swiper !== 'undefined') {
        new Swiper('.swiper-container', {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }

    // Xử lý thông báo
    const notification = document.getElementById('notification');
    if (notification) {
        if (notification.style.display !== 'none') {
            setTimeout(() => notification.style.display = 'none', 3000);
        }
        const closeBtn = notification.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => notification.style.display = 'none');
        }
    }

    // Xử lý thêm sản phẩm vào giỏ hàng
    document.querySelectorAll('.order-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const productId = button.getAttribute('data-id');
            const formData = new FormData();
            formData.append('product_id', productId);

            try {
                const response = await fetch('php/order.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (notification) {
                    notification.className = data.success ? 'success' : 'error';
                    notification.querySelector('span').textContent = data.success ? 'Đặt hàng thành công!' : (data.message || 'Vui lòng đăng nhập để đặt hàng.');
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Đã có lỗi xảy ra.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            }
        });
    });

    // Xử lý menu dropdown
    const avatar = document.querySelector('.avatar');
    const dropdown = document.querySelector('.dropdown');
    if (avatar && dropdown) {
        avatar.addEventListener('click', () => dropdown.classList.toggle('active'));
        document.addEventListener('click', (event) => {
            if (!avatar.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    }

    // Xử lý form đăng nhập
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('php/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'index.php';
                } else if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = data.message;
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Đã có lỗi xảy ra.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            }
        });
    }

    // Xử lý form đăng ký
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);

            try {
                const response = await fetch('php/register.php', { method: 'POST', body: new URLSearchParams(formData) });
                const data = await response.json();
                if (notification) {
                    notification.className = data.success ? 'success' : 'error';
                    notification.querySelector('span').textContent = data.message;
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                    if (data.success) {
                        setTimeout(() => {
                            document.getElementById('register-section').style.display = 'none';
                            document.getElementById('login-section').style.display = 'block';
                        }, 2000);
                    }
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Đã có lỗi xảy ra.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            }
        });
    }

    // Xử lý form quên mật khẩu (username)
    const forgotForm = document.getElementById('forgot-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('forgot-username').value;

            try {
                const response = await fetch('php/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `username=${encodeURIComponent(username)}`
                });
                const data = await response.json();
                if (notification) {
                    notification.className = data.success ? 'success' : 'error';
                    notification.querySelector('span').textContent = data.message;
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Đã có lỗi xảy ra.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            }
        });
    }

    // Xử lý form quên mật khẩu (email)
    const forgotEmailForm = document.getElementById('forgot-email-form');
    if (forgotEmailForm) {
        forgotEmailForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('forgot-email').value;

            try {
                const response = await fetch('php/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}`
                });
                const data = await response.json();
                if (notification) {
                    notification.className = data.success ? 'success' : 'error';
                    notification.querySelector('span').textContent = data.message;
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Đã có lỗi xảy ra.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 5000);
                }
            }
        });
    }

    // Chuyển đổi giữa các form
    const sectionMap = {
        'show-register': ['login-section', 'register-section'],
        'show-forgot': ['login-section', 'forgot-section'],
        'show-login-from-register': ['register-section', 'login-section'],
        'show-login-from-forgot': ['forgot-section', 'login-section'],
        'show-forgot-email': ['forgot-section', 'forgot-email-section'],
        'show-forgot-username': ['forgot-email-section', 'forgot-section'],
        'show-login-from-forgot-email': ['forgot-email-section', 'login-section']
    };

    Object.keys(sectionMap).forEach(id => {
        const link = document.getElementById(id);
        if (link) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const [hideSection, showSection] = sectionMap[id];
                document.getElementById(hideSection).style.display = 'none';
                document.getElementById(showSection).style.display = 'block';
                ['login-section', 'register-section', 'forgot-section', 'forgot-email-section'].forEach(section => {
                    if (section !== showSection && section !== hideSection) {
                        document.getElementById(section).style.display = 'none';
                    }
                });
            });
        }
    });

    // Xử lý Cropper cho ảnh đại diện
    let cropper;
    const cropperInput = document.getElementById('avatarInput');
    const cropperModal = document.getElementById('cropper-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const cropBtn = document.getElementById('crop-btn');
    const cancelBtn = document.getElementById('cancel-btn');

    if (cropperModal) {
        cropperModal.style.display = 'none';
    }

    if (cropperInput) {
        cropperInput.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (file.size > 1024 * 1024) {
                    if (notification) {
                        notification.className = 'error';
                        notification.querySelector('span').textContent = 'Dung lượng file tối đa 1MB.';
                        notification.style.display = 'block';
                        setTimeout(() => notification.style.display = 'none', 3000);
                    }
                    cropperInput.value = '';
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    if (notification) {
                        notification.className = 'error';
                        notification.querySelector('span').textContent = 'Chỉ hỗ trợ định dạng .JPEG hoặc .PNG.';
                        notification.style.display = 'block';
                        setTimeout(() => notification.style.display = 'none', 3000);
                    }
                    cropperInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    imageToCrop.src = event.target.result;
                    cropperModal.style.display = 'flex';
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.8,
                        movable: true,
                        zoomable: true,
                        rotatable: true,
                        scalable: false,
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (cropBtn) {
        cropBtn.addEventListener('click', async () => {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
            const imageData = canvas.toDataURL('image/jpeg');

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `cropped_image=${encodeURIComponent(imageData)}`
                });
                const data = await response.json();
                if (notification) {
                    notification.className = data.success ? 'success' : 'error';
                    notification.querySelector('span').textContent = data.message;
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 3000);
                    if (data.success) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                }
            } catch (error) {
                console.error('Lỗi:', error);
                if (notification) {
                    notification.className = 'error';
                    notification.querySelector('span').textContent = 'Lỗi khi lưu avatar.';
                    notification.style.display = 'block';
                    setTimeout(() => notification.style.display = 'none', 3000);
                }
            }

            cropperModal.style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
}
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            cropperModal.style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (cropperInput) {
                cropperInput.value = '';
            }
        });
    }
});