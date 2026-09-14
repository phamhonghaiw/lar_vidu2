<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - Trang khách hàng</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .nav-link-btn {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            color: #007bff;
            cursor: pointer;
        }
        .nav-link-btn:hover {
            text-decoration: underline;
        }

        /* Chat UI Styles */
        #chat-box {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 320px;
            z-index: 9999;
        }

        #chat-messages {
            height: 250px;
            overflow-y: auto;
            background-color: #f9f9f9;
            padding: 10px;
        }

        .message-row {
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .user-msg {
            text-align: right;
            color: #007bff;
        }

        .admin-msg {
            text-align: left;
            color: #333;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('welcome') }}">MyShop</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('welcome') }}">Trang chủ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.categories.index') }}">📂 Danh mục</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.cart.index') }}">🛒 Giỏ hàng</a>
            </li>
        </ul>

        <ul class="navbar-nav">
            @auth
                @if(Auth::user()->role === 'user')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.orders.index') }}">📦 Lịch sử đơn</a>
                    </li>
                @endif
                <li class="nav-item">
                    <span class="nav-link">👋 Chào, <strong>{{ Auth::user()->name }}</strong></span>
                </li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="form-inline">
                        @csrf
                        <button type="submit" class="nav-link-btn nav-link">🚪 Đăng xuất</button>
                    </form>
                </li>
            @endauth

            @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Đăng ký</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                </li>
            @endguest
        </ul>
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

@auth
<div id="chat-box">
    <button id="chat-toggle" class="btn btn-primary rounded-circle shadow">💬 Chat</button>

    <div id="chat-popup" class="card shadow-lg" style="display:none; border-radius: 10px;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>Hỗ trợ khách hàng</span>
            <button id="chat-close" class="btn btn-sm btn-light">X</button>
        </div>

        <div id="chat-messages" class="card-body">
            <small class="text-muted">Đang tải lịch sử...</small>
        </div>

        <div class="card-footer bg-white">
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                <div class="input-group-append">
                    <button id="send-btn" class="btn btn-success">Gửi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("chat-toggle");
    const chatPopup = document.getElementById("chat-popup");
    const closeBtn = document.getElementById("chat-close");
    const sendBtn = document.getElementById("send-btn");
    const input = document.getElementById("chat-input");
    const chatBox = document.getElementById("chat-messages");

    if (!toggleBtn) return; // Nếu khách chưa đăng nhập thì không chạy script chat

    // --- MỞ / ĐÓNG CHAT ---
    toggleBtn.onclick = () => {
        chatPopup.style.display = "block";
        toggleBtn.style.display = "none";
        loadMessages();
    };

    closeBtn.onclick = () => {
        chatPopup.style.display = "none";
        toggleBtn.style.display = "block";
    };

    // --- LOAD TIN NHẮN ---
    function loadMessages() {
        fetch("{{ route('user.chat.messages') }}")
            .then(res => res.json())
            .then(messages => {
                let html = "";
                if(messages.length === 0) {
                    html = "<div class='text-center text-muted'><small>Bắt đầu cuộc trò chuyện với Admin</small></div>";
                }

                messages.forEach(msg => {
                    const isMe = msg.sender_id == "{{ Auth::id() }}";
                    html += `
                        <div class="message-row ${isMe ? 'user-msg' : 'admin-msg'}">
                            <strong>${isMe ? 'Bạn' : 'Admin'}:</strong> ${msg.content}
                        </div>
                    `;
                });

                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight; // Tự động cuộn xuống cuối
            })
            .catch(err => console.error("Lỗi tải tin nhắn:", err));
    }

    // --- GỬI TIN NHẮN ---
    function sendMessage() {
        let message = input.value.trim();
        if (message === "") return;

        // Vô hiệu hóa input/button khi đang gửi để tránh gửi lặp
        input.disabled = true;
        sendBtn.disabled = true;

        fetch("{{ route('user.chat.send') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            input.value = "";
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
            loadMessages(); // Cập nhật lại khung chat ngay lập tức
        })
        .catch(err => {
            console.error("Lỗi gửi tin:", err);
            input.disabled = false;
            sendBtn.disabled = false;
        });
    }

    // Sự kiện Click nút Gửi
    sendBtn.onclick = sendMessage;

    // Sự kiện nhấn phím Enter
    input.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            sendMessage();
        }
    });

    // --- AUTO REFRESH (3 giây/lần) ---
    setInterval(() => {
        if (chatPopup.style.display === "block") {
            loadMessages();
        }
    }, 3000);

});
</script>

</body>
</html>