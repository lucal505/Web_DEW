const apiUrl = 'http://localhost:8081/api/admin.php';
const msgDiv = document.getElementById('message');
const loginForm = document.getElementById('loginForm');
const uploadSection = document.getElementById('uploadSection');

// afiseaza mesaj de succes sau eroare
function showMessage(text, isError = false) {
    msgDiv.textContent = text;
    msgDiv.style.color = isError ? 'red' : 'green';
}

// verifica daca userul este logat
function checkSession() {
    fetch(`${apiUrl}?action=check_session`)
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                loginForm.style.display = 'none';
                uploadSection.style.display = 'block';
            } else {
                loginForm.style.display = 'block';
                uploadSection.style.display = 'none';
            }
        });
}

// login
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('username', document.getElementById('username').value);
    formData.append('password', document.getElementById('password').value);

    fetch(apiUrl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage('Login success!');
                checkSession();
            } else {
                showMessage(data.message, true);
            }
        });
});

// upload
document.getElementById('uploadForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('fileToUpload', document.getElementById('fileToUpload').files[0]);

    fetch(apiUrl, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message);
                document.getElementById('uploadForm').reset();
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(() => showMessage('A network error occurred.', true));
});

// import all files in uploads/ directory
document.getElementById('importAllBtn').addEventListener('click', () => {
            const formData = new FormData();
            formData.append('action', 'import_all');

            showMessage('Importing all files, please wait...');

            fetch(apiUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message);
                    } else {
                        showMessage(data.message, true);
                    }
                })
                .catch(() => showMessage('A network error occurred.', true));
        });

// logout
document.getElementById('logoutBtn').addEventListener('click', () => {
    fetch(`${apiUrl}?action=logout`)
        .then(() => {
            showMessage('Logged out.');
            checkSession();
        });
});

// check if user is logged in
checkSession();