<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin mật khẩu mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #ff0022;
            color: #ffffff;
            padding: 40px 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
        }
        .content {
            padding: 40px;
            text-align: center;
        }
        h2 {
            color: #333333;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 20px;
            color: #666666;
            line-height: 1.5;
        }
        strong {
            font-weight: bold;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 40px 20px;
            text-align: center;
        }
        .footer p {
            margin: 0;
            color: #666666;
            line-height: 1.5;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thông tin mật khẩu mới</h1>
        </div>
        <div class="content">
            <h2>Xin chào {{$name}},</h2>
            <p>Chúng tôi đã nhận được yêu cầu từ bạn để thiết lập lại mật khẩu cho tài khoản của bạn.</p>
            <p>Dưới đây là mật khẩu mới của bạn:</p>
            <p><strong>Mật khẩu mới:</strong> "<i><strong>{{$newPass}}</strong></i>",</p>
            <p>Lưu ý:mật khẩu sẽ hết hạn sau <b>15 phút</b>!</p>
            <p>Vui lòng đăng nhập vào hệ thống để <b>"ĐỔI MẬT KHẨU MỚI"</b> một cách an toàn và không chia sẻ với người khác. Nếu bạn không thực hiện được thao tác này, vui lòng liên hệ với chúng tôi ngay lập tức để báo cáo.</p>
            <p>Trân trọng,</p>
            <p><i><b>EDUQUEST</b></i></p>
        </div>
        <div class="footer">
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ chúng tôi qua email này hoặc qua số điện thoại:<a href="tel:+84 123 456 789" style="color: #007bff"> +84 123 456 789</a>.</p>
        </div>
    </div>
</body>
</html>
