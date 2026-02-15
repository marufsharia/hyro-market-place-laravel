import Navbar from '@/Components/Navbar';
import { Head } from '@inertiajs/react';

export default function MarketLayout({ title, children }) {
    return (
        <div className="min-h-screen bg-slate-50 flex flex-col font-sans">
            <Head title={title} />
            <Navbar />
            <main className="flex-1">
                {children}
            </main>
            <footer className="bg-white border-t border-slate-200 py-6 mt-12 text-center text-slate-500 text-sm">
                © 2023 Hyro Market Place. Powered by Laravel & Inertia.
            </footer>
        </div>
    );
}