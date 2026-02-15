import { Head, Link, router } from '@inertiajs/react';
import MarketLayout from '@/Components/MarketLayout';

export default function Show({ plugin }) {
    const handleDownload = () => {
        router.post(route('market.download', plugin.slug));
    };

    return (
        <MarketLayout title={`${plugin.name} - Hyro Market`}>
            <div className="max-w-4xl mx-auto px-4 py-8">
                <Link href={route('market.index')} className="text-teal-600 hover:underline text-sm mb-4 block">
                    ← Back to Marketplace
                </Link>

                <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div className="p-8 flex flex-col md:flex-row gap-8 border-b border-slate-100">
                        <img src={`/storage/${plugin.logo_path}`} className="w-32 h-32 rounded-xl object-cover shadow-sm" />
                        <div className="flex-1">
                            <h1 className="text-3xl font-bold text-slate-900 mb-2">{plugin.name}</h1>
                            <p className="text-slate-600 mb-4">{plugin.description}</p>

                            <div className="flex flex-wrap gap-3 text-sm">
                                <span className="bg-slate-100 px-3 py-1 rounded-full">v{plugin.version}</span>
                                <span className="bg-blue-50 text-blue-700 px-3 py-1 rounded-full">{plugin.license_type}</span>
                                <span className="bg-purple-50 text-purple-700 px-3 py-1 rounded-full">{plugin.compatibility}</span>
                            </div>
                        </div>
                    </div>

                    <div className="p-8 bg-slate-50 flex justify-between items-center">
                        <div className="text-sm text-slate-500">
                            Downloads: <span className="font-bold text-slate-900">{plugin.downloads}</span>
                        </div>
                        <button
                            onClick={handleDownload}
                            className="bg-slate-900 text-white px-6 py-3 rounded-lg font-bold hover:bg-teal-600 transition-colors flex items-center gap-2"
                        >
                            <span>↓</span> Download / Install
                        </button>
                    </div>
                </div>

                {/* Reviews Section */}
                <div className="mt-8">
                    <h2 className="text-xl font-bold mb-4">Reviews</h2>
                    {plugin.reviews.length > 0 ? (
                        <div className="space-y-4">
                            {plugin.reviews.map(review => (
                                <div key={review.id} className="bg-white p-4 rounded-lg shadow-sm border border-slate-100">
                                    <div className="flex justify-between mb-2">
                                        <span className="font-bold">{review.user.name}</span>
                                        <span className="text-amber-500">{'★'.repeat(review.rating)}</span>
                                    </div>
                                    <p className="text-slate-600 text-sm">{review.comment}</p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-slate-500">No reviews yet.</p>
                    )}
                </div>
            </div>
        </MarketLayout>
    );
}