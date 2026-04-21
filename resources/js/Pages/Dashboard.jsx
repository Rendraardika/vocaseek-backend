import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { translate } from '@/lib/translations';

export default function Dashboard() {
    const { locale } = usePage().props;
    const t = (key, fallback) => translate(locale, key, fallback);
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {t('common.dashboard', 'Dashboard')}
                </h2>
            }
        >
            <Head title={t('common.dashboard', 'Dashboard')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {t('auth.loggedIn', "You're logged in!")}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
