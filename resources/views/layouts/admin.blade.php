<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin - Quản trị hệ thống')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        :root { --admin-sidebar-width: 180px; --admin-border: #e1e6ec; --admin-muted: #7d8b9c; }
        body.admin-layout { margin: 0; min-height: 100vh; font-family: Arial, sans-serif; font-size: 12px; color: #354252; background: #f3f5f8; }
        .admin-layout .sidebar { width: var(--admin-sidebar-width); position: fixed; inset: 0 auto 0 0; height: 100vh; overflow-y: auto; background: #343a40; color: #fff; z-index: 1050; display: flex; flex-direction: column; }
        .admin-brand { display: flex; align-items: center; gap: 9px; min-height: 56px; padding: 0 15px; letter-spacing: .3px; color: white; text-decoration: none; }
        .admin-brand:hover { color: white; text-decoration: none; }
        .admin-brand-mark { flex-shrink: 0; width: 17px; height: 17px; transform: rotate(45deg); background: #adb5bd; border: 3px solid #e9ecef; }
        .admin-brand-grid { margin-left: auto; color: #ced4da; font-size: 19px; }
        .admin-store { text-align: center; padding: 16px 12px 23px; border-bottom: 1px solid #ffffff16; margin-bottom: 10px; }
        .admin-avatar { display: flex; align-items: center; justify-content: center; background: #6c757d; border: 3px solid #e9ecef; width: 43px; height: 43px; border-radius: 50%; margin: 0 auto 12px; font-size: 20px; font-weight: 700; }
        .admin-store strong { display: block; overflow-wrap: anywhere; font-size: 12px; }
        .admin-store > span { display: block; font-size: 9px; color: #ced4da; letter-spacing: .7px; margin-top: 5px; }
        .admin-nav { display: flex; flex-direction: column; }
        .admin-nav a { display: flex; align-items: center; gap: 10px; padding: 12px 13px; border-left: 3px solid transparent; font-size: 12px; color: #f1f3f5; text-decoration: none; }
        .admin-nav a:hover { background: #495057; }
        .admin-nav a.sidebar-current { background: #495057; border-left-color: #ced4da; font-weight: 700; color: #fff; }
        .admin-icon { width: 16px; height: 16px; flex-shrink: 0; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; }
        .admin-nav .admin-icon { color: #c5ccd3; }
        .admin-logout { margin-top: auto; padding: 22px 15px 18px; }
        .admin-logout button { width: 100%; color: #e5e9ed; border: 1px solid #727b85; background: transparent; border-radius: 3px; padding: 8px; font-size: 11px; cursor: pointer; }
        .admin-logout button:hover { background: #495057; }
        .admin-shell { margin-left: var(--admin-sidebar-width); min-width: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .admin-topbar { display: flex; align-items: center; gap: 12px; min-height: 49px; padding: 10px 20px; background: #fff; border-bottom: 1px solid var(--admin-border); flex-shrink: 0; }
        .admin-breadcrumb { display: flex; gap: 9px; align-items: center; color: var(--admin-muted); font-size: 11px; }
        .admin-breadcrumb a { color: var(--admin-muted); }.admin-breadcrumb strong { color: #4f6074; font-weight: 600; }
        .admin-account { margin-left: auto; display: flex; align-items: center; gap: 8px; color: #758496; font-size: 11px; }
        .admin-account small { background: #edf1f5; color: #637286; border-radius: 3px; padding: 3px 6px; font-size: 9px; }
        .admin-layout .content { padding: 20px 20px 65px; flex: 1; min-width: 0; }
        .admin-layout .content > .container, .admin-layout .content > .container-fluid { max-width: none; padding-left: 0; padding-right: 0; }
        .admin-layout .content h1 { font-size: 22px; font-weight: 600; }.admin-layout .content h2 { font-size: 19px; font-weight: 600; }.admin-layout .content h3 { font-size: 17px; }.admin-layout .content h4 { font-size: 16px; }.admin-layout .content h5 { font-size: 14px; }
        .admin-layout .content .card { border: 1px solid var(--admin-border); border-radius: 4px; box-shadow: none !important; }
        .admin-layout .content .card-header { background: #f8fafc; border-bottom: 1px solid var(--admin-border); padding: 12px 15px; font-size: 12px; }
        .admin-layout .content .card-body { padding: 16px; }.admin-layout .content .card-footer { background: #fff; padding: 11px 15px; border-top: 1px solid var(--admin-border); }
        .admin-layout .content .btn { border-radius: 3px; font-size: 12px; padding: 6px 11px; }.admin-layout .content .btn-sm { font-size: 11px; padding: 4px 8px; }
        .admin-layout .content label { color: #65758a; font-size: 11px; margin-bottom: 5px; }
        .admin-layout .content .form-control { height: 33px; padding: 6px 9px; font-size: 12px; border-color: #dce2e9; border-radius: 3px; }
        .admin-layout .content textarea.form-control { height: auto; min-height: 90px; }
        .admin-layout .content .form-control:focus { border-color: #86a8d1; box-shadow: 0 0 0 2px #e4edf8; }
        .admin-layout .content .table { background: #fff; border: 1px solid var(--admin-border); color: #354252; font-size: 12px; }
        .admin-layout .content .table th { font-size: 11px; font-weight: 600; color: #647181; background: #e9edf1; border: 0; border-bottom: 1px solid #dae0e6; padding: 12px 10px; white-space: nowrap; }
        .admin-layout .content .table td { padding: 12px 10px; border: 0; border-bottom: 1px solid #edf0f3; vertical-align: middle; }
        .admin-layout .content .table tbody tr:nth-child(even) { background: #f5f7f9; }.admin-layout .content .table tbody tr:hover { background: #edf4ff; }
        .admin-layout .content .table .btn { white-space: nowrap; }
        .admin-layout .content .badge { font-weight: 500; border-radius: 3px; font-size: 10px; padding: 4px 6px; }
        .admin-layout .content .nav-pills { gap: 4px; padding: 6px; background: #fff; border: 1px solid var(--admin-border); border-radius: 4px; }
        .admin-layout .content .nav-pills .nav-link { padding: 8px 12px; font-size: 11px; border-radius: 3px; }
        .admin-layout .content .nav-pills .nav-link.active { background: #495057; }
        .admin-layout .content .pagination { margin-bottom: 0; flex-wrap: wrap; }.admin-layout .content .page-link { padding: 6px 10px; font-size: 11px; }
        .admin-table-scroll { overflow-x: auto; margin-bottom: 16px; }.admin-table-scroll > .table { margin-bottom: 0; }
        .admin-layout a:focus-visible, .admin-layout button:focus-visible { outline: 2px solid #86a8d1; outline-offset: 2px; }
        .admin-menu-button { display: none; align-items: center; justify-content: center; background: #fff; border: 1px solid #dce2e9; border-radius: 3px; width: 31px; height: 30px; color: #5e6d80; cursor: pointer; }
        .admin-backdrop { display: none; }
        #admin-chat-box { position: fixed; bottom: 10px; right: 12px; width: auto; z-index: 1040; }
        #chat-toggle { font-size: 11px; padding: 7px 11px; border-radius: 3px; }
        #chat-popup { display: none; width: 330px; height: 500px; max-height: calc(100vh - 64px); border: 1px solid var(--admin-border); border-radius: 5px; overflow: hidden; }
        #user-list { border-bottom: 1px solid #ddd; max-height: 120px; overflow-y: auto; background: #f3f5f8; }
        .user-item { padding: 8px 15px; cursor: pointer; border-bottom: 1px solid #e1e6ec; font-size: 12px; }.user-item:hover { background: #e9edf1; }.user-item.active { background: #495057; color: white; }
        #chat-messages { height: 220px; overflow-y: auto; padding: 10px; background: #fff; }.msg-row { margin-bottom: 5px; font-size: 12px; }
        @media (max-width: 767px) {
            .admin-layout .sidebar { transform: translateX(-100%); transition: transform .2s; box-shadow: 6px 0 18px #172d5626; }
            .admin-layout.admin-menu-open { overflow: hidden; }.admin-layout.admin-menu-open .sidebar { transform: translateX(0); }
            .admin-layout.admin-menu-open .admin-backdrop { display: block; position: fixed; inset: 0; z-index: 1045; background: #18263866; border: 0; cursor: pointer; }
            .admin-shell { margin-left: 0; }.admin-menu-button { display: inline-flex; }.admin-topbar { padding: 9px 12px; }.admin-account > span { display: none; }
            .admin-layout .content { padding: 15px 12px 65px; }.admin-table-scroll .table { min-width: 560px; }
            .admin-layout .content .nav-pills { flex-wrap: nowrap; overflow-x: auto; }.admin-layout .content .nav-link { white-space: nowrap; }
            #chat-popup { width: min(330px, calc(100vw - 24px)); }
        }
        @media (prefers-reduced-motion: reduce) { .admin-layout .sidebar { transition: none; } }
    </style>
    @stack('styles')
</head>
<body class="admin-layout @yield('body-class')">

@php
    $adminMenu = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z'],
        ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'label' => 'Sản phẩm', 'icon' => 'M3 7l9-4 9 4v10l-9 4-9-4z M3 7l9 4 9-4 M12 11v10'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Danh mục', 'icon' => 'M3 6h7l2 3h9v11H3z'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Người dùng', 'icon' => 'M8 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8 M2 21v-3a6 6 0 0 1 12 0v3 M16 4a4 4 0 0 1 0 8 M18 15a5 5 0 0 1 4 5'],
        ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'label' => 'Đơn hàng', 'icon' => 'M6 3h12v18H6z M9 7h6 M9 11h6 M9 15h4'],
        ['route' => 'admin.finance.index', 'match' => 'admin.finance.index', 'label' => 'Thống kê tài chính', 'icon' => 'M3 21V3 M3 21h18 M7 17v-4 M12 17V8 M17 17V5'],
        ['route' => 'admin.finance.transactions', 'match' => 'admin.finance.transactions', 'label' => 'Giao dịch thanh toán', 'icon' => 'M3 5h18v14H3z M3 9h18 M6 15h4'],
        ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'label' => 'Báo cáo', 'icon' => 'M5 3h10l4 4v14H5z M15 3v5h4 M9 17v-4 M13 17v-7'],
    ];
    $adminSection = collect($adminMenu)->first(fn ($item) => request()->routeIs($item['match']));
@endphp
<aside class="sidebar" id="admin-sidebar" aria-label="Menu quản trị">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark" aria-hidden="true"></span><strong>SHOP ADMIN</strong><span class="admin-brand-grid" aria-hidden="true">▦</span></a>
    <div class="admin-store">
        <div class="admin-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
        <strong>{{ auth()->user()->name }}</strong>
        <span>QUẢN LÝ BÁN HÀNG</span>
    </div>
    <nav class="admin-nav">
        @foreach($adminMenu as $item)
            <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['match']) ? 'sidebar-current' : '' }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                <svg class="admin-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg><span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <form action="{{ route('logout') }}" method="POST" class="admin-logout">
        @csrf
        <button type="submit">Đăng xuất</button>
    </form>
</aside>
<button class="admin-backdrop" id="admin-menu-backdrop" aria-label="Đóng menu quản trị" tabindex="-1" type="button"></button>
<div class="admin-shell">
    <header class="admin-topbar">
        <button type="button" class="admin-menu-button" id="admin-menu-toggle" aria-label="Mở menu quản trị" aria-controls="admin-sidebar" aria-expanded="false"><svg class="admin-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <nav class="admin-breadcrumb" aria-label="Vị trí trang"><a href="{{ route('admin.dashboard') }}">Quản trị</a><span aria-hidden="true">/</span><strong>{{ $adminSection['label'] ?? 'Tổng quan' }}</strong></nav>
        <div class="admin-account"><span>{{ auth()->user()->name }}</span><small>Quản trị viên</small></div>
    </header>
    <main class="content" id="admin-main-content">
        @yield('content')
    </main>
</div>

<div id="admin-chat-box">
    <button id="chat-toggle" class="btn btn-dark shadow">💬 Chat Khách hàng</button>

    <div id="chat-popup" class="card shadow-lg">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <strong>Hỗ trợ trực tuyến</strong>
            <button id="chat-close" class="btn btn-sm btn-light">X</button>
        </div>

        <div id="user-list">
            <div class="p-2 text-center text-muted"><small>Đang tải danh sách...</small></div>
        </div>

        <div id="chat-messages">
            <div class="text-center mt-5 text-muted">Chọn một khách hàng để xem tin nhắn</div>
        </div>

        <div class="card-footer bg-white">
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập câu trả lời...">
                <div class="input-group-append">
                    <button id="send-btn" class="btn btn-success btn-sm">Gửi</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentUserId = null;
const chatPopup = document.getElementById("chat-popup");
const chatMessages = document.getElementById("chat-messages");
const chatInput = document.getElementById("chat-input");

// Mở/Đóng popup
document.getElementById("chat-toggle").onclick = () => {
    chatPopup.style.display = "block";
    loadUsers();
};
document.getElementById("chat-close").onclick = () => {
    chatPopup.style.display = "none";
};

// 1. Load danh sách User đã từng nhắn tin
function loadUsers() {
    fetch("{{ route('admin.chat.users') }}")
        .then(res => res.json())
        .then(users => {
            let html = "";
            users.forEach(user => {
                let activeClass = (currentUserId == user.id) ? 'active' : '';
                html += `<div class="user-item ${activeClass}" onclick="selectUser(${user.id}, this)">
                            ${user.name}
                         </div>`;
            });
            document.getElementById("user-list").innerHTML = html || '<div class="p-2 text-muted">Chưa có hội thoại</div>';
        });
}

// 2. Chọn User để chat
function selectUser(userId, element) {
    currentUserId = userId;
    // Highlight user được chọn
    document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    loadMessages();
}

// 3. Load tin nhắn của User đang được chọn
function loadMessages() {
    if (!currentUserId) return;

    fetch(`/admin/chat/messages/${currentUserId}`)
        .then(res => res.json())
        .then(messages => {
            let html = "";
            messages.forEach(msg => {
                let senderName = msg.sender_id == "{{ Auth::id() }}" ? "Bạn" : msg.sender.name;
                let color = msg.sender_id == "{{ Auth::id() }}" ? "blue" : "black";
                html += `<div class="msg-row" style="color: ${color}">
                            <strong>${senderName}:</strong> ${msg.content}
                         </div>`;
            });
            chatMessages.innerHTML = html;
            chatMessages.scrollTop = chatMessages.scrollHeight; // Cuộn xuống cuối
        });
}

// 4. Gửi tin nhắn cho User
function sendMessage() {
    let message = chatInput.value.trim();
    if (!message || !currentUserId) return;

    fetch("{{ route('admin.chat.send') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            message: message,
            user_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        chatInput.value = "";
        loadMessages();
    })
    .catch(err => console.error("Lỗi gửi tin:", err));
}

document.getElementById("send-btn").onclick = sendMessage;
chatInput.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

// 5. Polling (Tự động cập nhật mỗi 3 giây)
setInterval(() => {
    if (chatPopup.style.display === "block") {
        loadMessages();
        loadUsers(); // Cập nhật danh sách nếu có người mới nhắn
    }
}, 3000);
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const backdrop = document.getElementById('admin-menu-backdrop');
    const mobile = window.matchMedia('(max-width: 767px)');
    const setMenu = open => {
        document.body.classList.toggle('admin-menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Đóng menu quản trị' : 'Mở menu quản trị');
        sidebar.inert = mobile.matches && !open;
        if (open) sidebar.querySelector('a').focus();
    };
    toggle.addEventListener('click', () => setMenu(!document.body.classList.contains('admin-menu-open')));
    backdrop.addEventListener('click', () => { setMenu(false); toggle.focus(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('admin-menu-open')) {
            setMenu(false);
            toggle.focus();
        }
    });
    mobile.addEventListener('change', () => setMenu(false));
    setMenu(false);

    document.querySelectorAll('.content table.table').forEach(table => {
        if (table.closest('.table-responsive, .admin-table-scroll')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'admin-table-scroll';
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', 'Bảng dữ liệu, có thể cuộn ngang');
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
});
</script>

@stack('scripts')
</body>
</html>
