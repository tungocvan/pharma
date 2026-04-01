import Echo from "laravel-echo";
import { io } from "socket.io-client";

// Lấy cấu hình từ Window Object (được set trong master layout)
const SOCKET_HOST = window.CHAT_CONFIG_HOST || window.location.hostname + ":6002";

window.io = io;

/**
 * Khởi tạo Laravel Echo với Driver Socket.io
 */
window.Echo = new Echo({
    broadcaster: "socket.io",
    client: io,
    host: SOCKET_HOST,
    transports: ["websocket", "polling"],
    withCredentials: false
});

// Log trạng thái phục vụ debug
window.Echo.connector.socket.on("connect", () => {
    console.log("✅ SOCKET_HOST: ", SOCKET_HOST);
    console.log("✅ Chat Realtime Connected. ID:", window.Echo.connector.socket.id);
});

window.Echo.connector.socket.on("connect_error", (error) => {
    console.error("❌ Chat Connection Error:", error);
});

// window.Echo.connector.socket.onAny((eventName, data) => {
//     console.log("🔔 Tín hiệu thô nhận được từ Socket:", eventName, data);
// });
