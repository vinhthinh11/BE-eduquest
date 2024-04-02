<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Trưởng Bộ Môn</title>
</head>

<body>
    <h2>Create Trưởng bộ môn</h2>
    <form id="createTBMForm">
        @csrf
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name"><br>

        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email"><br>

        <label for="birthday">Birthday:</label><br>
        <input type="date" id="birthday" name="birthday"><br>

        <label for="gender">Gender:</label><br>
        <select id="gender" name="gender">
            <option value="1">Male</option>
            <option value="2">Female</option>
            <option value="3">Other</option>
        </select><br><br>
        <select id="subject" name="subject">
            <option value="1">Toán Học</option>
            <option value="2">Ngữ Văn</option>
            <option value="3">Tiếng Anh</option>
            <option value="4">Vật Lý</option>
            <option value="5">Hóa Học</option>
            <option value="6">Sinh Học</option>
            <option value="7">Lịch Sử</option>
            <option value="8">Địa Lý</option>
            <option value="9">GDCD</option>
        </select><br><br>

        <button type="submit">Create</button>
    </form>
    <div id="message"></div>


    <h2>Trưởng bộ môn List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Birthday</th>
                <th>Gender</th>
                <th>Subject</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="tbmList">
            <!-- Dữ liệu TBM sẽ được thêm vào đây -->
        </tbody>
    </table>

    <div id="message"></div>

    <script>
        // Lấy danh sách TBM khi trang được tải
        document.addEventListener("DOMContentLoaded", function() {
            fetchTBMList();
        });

        // Hàm gửi yêu cầu lấy danh sách TBM
        function fetchTBMList() {
            fetch('/truongbomon/get')
                .then(response => response.json())
                .then(data => {
                    displayTBMList(data.getAllTBM);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Hàm hiển thị danh sách TBM
        function displayTBMList(tbm) {
            const tbmList = document.getElementById('tbmList');
            tbmList.innerHTML = ''; // Xóa nội dung cũ
            subject_head.forEach(subject_head => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${subject_head.subject_head_id}</td>
                    <td>${subject_head.name}</td>
                    <td>${subject_head.username}</td>
                    <td>${subject_head.email}</td>
                    <td>${subject_head.birthday}</td>
                    <td>${subject_head.gender_id}</td>
                    <td>${subject_head.subject_id}</td>
                    <td>
                        <button onclick="deleteAdmin(${subject_head.subject_head_id})">Delete</button>
                        <button onclick="updateAdmin(${subject_head.subject_head_id})">Update</button>
                    </td>
                `;
                tbmList.appendChild(row);
            });
        }

        // Hàm xóa admin
        function deleteTBM(tbmId) {
            fetch('/truongbomon/delete-tbm', {
                    method: 'DELETE',
                    headers: {
                        '   Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        subject_head_id: tbmId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    fetchTBMList(); // Sau khi xóa, làm mới danh sách tbm
                    document.getElementById('message').textContent = data.message;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('message').textContent = 'Error occurred while deleting TBM';
                });
        }


        // Hàm cập nhật tbm
        function updateTBM(tbmId) {
            // TODO: Thêm mã để cập nhật admin
        }
        document.getElementById('createTBMForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            fetch('/truongbomon/create-tbm', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('message').textContent = data.result.status_value;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
</body>

</html>

