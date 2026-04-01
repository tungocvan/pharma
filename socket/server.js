require('dotenv').config();
const cors = require('cors');
const express = require("express");
const { createServer } = require("http");
const { Server } = require("socket.io");

const app = express();
app.use(cors());
app.use(express.json());

const httpServer = createServer(app);
const io = new Server(httpServer, {
    cors: {
        origin: process.env.APP_URL || "*", // Chỉ định domain Laravel để bảo mật
        methods: ["GET", "POST"],
    }
});

/**
 * Middleware: Bảo mật kênh truyền từ Laravel
 */
const bridgeAuth = (req, res, next) => {
    console.log("🔌 process.env.BRIDGE_SECRET_KEY:", process.env.BRIDGE_SECRET_KEY);
    const secret = req.headers['x-bridge-secret'];
    console.log("🔌 secret:", secret);
    if (secret !== process.env.BRIDGE_SECRET_KEY) {
        return res.status(401).json({ error: 'Unauthorized bridge request' });
    }
    next();
};

// Route nhận tin nhắn từ Laravel Bridge (Đã thêm Auth)
app.post("/broadcast", bridgeAuth, (req, res) => {
    const { channel, event, data } = req.body;

    // Phát tín hiệu tới đúng channel hoặc broadcast toàn cục
    // Ở bản này, chúng ta dùng emit đơn giản, có thể mở rộng sang rooms
    io.emit(event, data);

    res.json({ ok: true });
});

app.get('/health', (req, res) => {
    const incomingSecret = req.headers['x-bridge-secret'];
    const localSecret = process.env.BRIDGE_SECRET_KEY;

    if (incomingSecret !== localSecret) {
        return res.status(401).json({ status: 'Unauthorized' });
    }
    res.json({ status: 'ok', message: 'NodeJS is alive' });
});

io.on("connection", (socket) => {
    console.log("🔌 Client connected:", socket.id);

    socket.on("join", (room) => {
        socket.join(room);
        console.log(`👤 Socket ${socket.id} joined room: ${room}`);
    });

    socket.on("disconnect", () => {
        console.log("❌ Client disconnected:", socket.id);
    });
});

const PORT = process.env.PORT || 6003;
httpServer.listen(PORT, "0.0.0.0", () => {
    console.log(`🚀 Realtime Server running at port ${PORT}`);
});
