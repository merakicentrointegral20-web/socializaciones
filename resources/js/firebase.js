// Firebase SDK Initialization for MERAKI
import { initializeApp } from "firebase/app";

export const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || "AIzaSyCPPjgzPP6Mxq6RSaA0L_uhyAiMsYIYNB8",
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || "socializaciones-meraki.firebaseapp.com",
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || "socializaciones-meraki",
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || "socializaciones-meraki.firebasestorage.app",
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || "166359180202",
    appId: import.meta.env.VITE_FIREBASE_APP_ID || "1:166359180202:web:ae81d34fa7ec86500d81d3"
};

export const firebaseApp = initializeApp(firebaseConfig);

if (typeof window !== "undefined") {
    window.firebaseApp = firebaseApp;
    window.firebaseConfig = firebaseConfig;
}

export default firebaseApp;
