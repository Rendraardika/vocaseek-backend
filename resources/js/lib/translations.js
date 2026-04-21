export const translations = {
    id: {
        common: {
            dashboard: 'Dashboard',
            profile: 'Profil',
            logout: 'Keluar',
            login: 'Masuk',
            register: 'Daftar',
            email: 'Email',
            password: 'Kata Sandi',
            language: 'Bahasa',
            name: 'Nama',
            confirmPassword: 'Konfirmasi Kata Sandi',
            rememberMe: 'Ingat saya',
            forgotPassword: 'Lupa kata sandi?',
            continueWithGoogle: 'Lanjut dengan Google',
            alreadyRegistered: 'Sudah punya akun?',
        },
        welcome: {
            title: 'Selamat Datang',
            docsTitle: 'Dokumentasi',
            docsText: 'Laravel memiliki dokumentasi yang sangat baik untuk setiap bagian framework.',
            laracastsTitle: 'Laracasts',
            laracastsText: 'Laracasts menyediakan ribuan video tutorial Laravel, PHP, dan JavaScript.',
            newsTitle: 'Laravel News',
            newsText: 'Laravel News adalah portal komunitas yang merangkum kabar terbaru ekosistem Laravel.',
            ecosystemTitle: 'Ekosistem yang Kuat',
            ecosystemText: 'Perangkat dan library Laravel membantu proyek berkembang lebih cepat.',
        },
        auth: {
            loggedIn: 'Anda berhasil masuk!',
        },
    },
    en: {
        common: {
            dashboard: 'Dashboard',
            profile: 'Profile',
            logout: 'Log Out',
            login: 'Log in',
            register: 'Register',
            email: 'Email',
            password: 'Password',
            language: 'Language',
            name: 'Name',
            confirmPassword: 'Confirm Password',
            rememberMe: 'Remember me',
            forgotPassword: 'Forgot your password?',
            continueWithGoogle: 'Continue with Google',
            alreadyRegistered: 'Already registered?',
        },
        welcome: {
            title: 'Welcome',
            docsTitle: 'Documentation',
            docsText: 'Laravel has wonderful documentation covering every aspect of the framework.',
            laracastsTitle: 'Laracasts',
            laracastsText: 'Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript.',
            newsTitle: 'Laravel News',
            newsText: 'Laravel News is a community driven portal for the latest Laravel ecosystem updates.',
            ecosystemTitle: 'Vibrant Ecosystem',
            ecosystemText: 'Laravel tools and libraries help your projects move faster and scale better.',
        },
        auth: {
            loggedIn: "You're logged in!",
        },
    },
};

export function translate(locale, key, fallback = key) {
    return key.split('.').reduce((value, part) => value?.[part], translations[locale]) ?? fallback;
}
