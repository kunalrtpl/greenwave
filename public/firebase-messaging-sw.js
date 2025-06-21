importScripts("https://www.gstatic.com/firebasejs/7.16.1/firebase-app.js");
importScripts(
    "https://www.gstatic.com/firebasejs/7.16.1/firebase-messaging.js",
);
// For an optimal experience using Cloud Messaging, also add the Firebase SDK for Analytics.
importScripts(
    "https://www.gstatic.com/firebasejs/7.16.1/firebase-analytics.js",
);
// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
    //Kunal Testing details
    apiKey: "AIzaSyB-LrF0UK-pbDS5NB_kBJOg0dcYnTrvHeM",
    authDomain: "coral-arbor-159107.firebaseapp.com",
    projectId: "coral-arbor-159107",
    storageBucket: "coral-arbor-159107.appspot.com",
    messagingSenderId: "354914230669",
    appId: "1:354914230669:web:15e6b5d591ae42f4ef9dc4",
    measurementId: "G-EG9LWLDJ07"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    console.log('SW notification click event', event);
    //console.log(event.notification.data.url);
    var openUrl = event.notification.data.url;
    if (openUrl !== undefined){
        clients.openWindow(event.notification.data.url);
    }else{
        clients.openWindow(event.notification.data.FCM_MSG.notification.click_action);
    }
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload,
    );
    // Customize notification here
    const notificationTitle = "Background Message Title";
    const notificationOptions = {
        /*body: "Background Message body.",
        icon: "https://roooting.softwaresolutions.website/bell.png",*/
    };
    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});
