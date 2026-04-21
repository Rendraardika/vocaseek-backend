import api from '@/api';
import { router, usePage } from '@inertiajs/react';
import { translate } from '@/lib/translations';
import { useEffect, useState } from 'react';

const localeLabels = {
    id: 'Indonesia',
    en: 'English',
};

export default function LanguageSwitcher() {
    const { auth, locale, availableLocales = [] } = usePage().props;
    const t = (key, fallback) => translate(locale, key, fallback);
    const [selectedLocale, setSelectedLocale] = useState(locale);

    useEffect(() => {
        setSelectedLocale(locale);
    }, [locale]);

    const handleChange = async (e) => {
        const nextLocale = e.target.value;
        const token = window.localStorage.getItem('token');

        setSelectedLocale(nextLocale);
        window.localStorage.setItem('locale', nextLocale);

        try {
            if (auth?.user && token) {
                await api.put('/language', { locale: nextLocale });
            }

            router.post(
                route('locale.switch'),
                { locale: nextLocale },
                { preserveScroll: true, preserveState: true },
            );
        } catch (error) {
            setSelectedLocale(locale);
            window.localStorage.setItem('locale', locale);
            console.error('Failed to update locale preference.', error);
        }
    };

    return (
        <div className="flex items-center gap-2 text-sm text-gray-600">
            <span>{t('common.language', 'Language')}</span>
            <select
                value={selectedLocale}
                onChange={handleChange}
                className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                {availableLocales.map((item) => (
                    <option key={item} value={item}>
                        {localeLabels[item] ?? item.toUpperCase()}
                    </option>
                ))}
            </select>
        </div>
    );
}
