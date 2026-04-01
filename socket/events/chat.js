/**
 * Chat Event Module - Enterprise Edition
 * @param {import('socket.io').Server} io
 * @param {Function} bridgeAuth Middleware xác thực từ Laravel
 * @param {import('express').Application} app
 */
module.exports = (io, bridgeAuth, app) => {

    /**
     * 1. BRIDGE TỪ LARAVEL (HTTP)
     * Nhận các sự kiện: MessageSent, MessageDeleted
     */
    app.post("/broadcast", bridgeAuth, (req, res) => {
        const { event, data } = req.body;

        // Log tín hiệu từ Laravel
        console.log(`📡 [Bridge] Event: ${event} | Session: ${data.session_id || 'N/A'}`);

        // Phát tín hiệu tới toàn bộ client đang kết nối
        io.emit(event, data);

        res.json({ ok: true });
    });

    /**
     * 2. SOCKET EVENTS (REALTIME TRỰC TIẾP)
     * Nhận các sự kiện: join-session, typing
     */
    io.on("connection", (socket) => {

        // Lắng nghe lệnh gia nhập phòng của phiên chat
        socket.on("join-session", (sessionId) => {
            if (!sessionId) return;

            const roomName = `session-${sessionId}`;
            socket.join(roomName);

            console.log(`🚪 [Room] Socket ${socket.id} joined: ${roomName}`);
        });

        // Chuyển tiếp (Relay) tín hiệu đang soạn tin
        socket.on("typing", (data) => {
            if (!data.session_id) return;
            const roomName = `session-${data.session_id}`;

            // Gửi cho người còn lại trong phòng
            socket.to(roomName).emit("display-typing", data);
        });

        // Xử lý khi ngắt kết nối
        socket.on("disconnect", () => {
            console.log(`❌ [Disconnect] Socket ${socket.id} left.`);
        });
    });



};
