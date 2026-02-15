import { Head, Link, useForm } from '@inertiajs/react';
import Navbar from '@/Components/Navbar';
import PluginCard from '@/Components/PluginCard';

export default function Index({ plugins, categories, filters }) {
    const { data, get } = useForm({
        search: filters.search || '',
        category: filters.category || 'All',
    });

    const handleFilterChange = (key, value) => {
        data[key] = value;
        get(route('market.index'), { preserveState: true });
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 font-sans">
            <Head title="Marketplace" />
            <Navbar />
            
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="mb-8 text-center sm:text-left sm:flex sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-extrabold text-slate-900 dark:text-white">
                            Explore <span className="text-teal-600">Plugins</span>
                        </h1>
                    </div>
                    <Link href={route('admin.dashboard')} className="mt-4 sm:mt-0 bg-slate-900 dark:bg-teal-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:opacity-90">
                        Submit Plugin
                    </Link>
                </div>

                <div className="flex flex-col md:flex-row gap-4 mb-8">
                    <input
                        type="text"
                        value={data.search}
                        onChange={(e) => handleFilterChange('search', e.target.value)}
                        placeholder="Search plugins..."
                        className="flex-1 px-4 py-2 rounded-full border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    />
                    
                    <div className="flex gap-2 overflow-x-auto pb-2">
                        {['All', ...categories.map(c => c.name)].map(cat => (
                            <button
                                key={cat}
                                onClick={() => handleFilterChange('category', cat)}
                                className={`whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors ${
                                    data.category === cat
                                        ? 'bg-teal-600 text-white'
                                        : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
                                }`}
                            >
                                {cat}
                            </button>
                        ))}
                    </div>
                </div>

                {plugins.data.length > 0 ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {plugins.data.map(plugin => (
                            <PluginCard key={plugin.id} plugin={plugin} />
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-20 text-slate-500 dark:text-slate-400">No plugins found.</div>
                )}

                {plugins.links && (
                    <div className="mt-8 flex justify-center gap-2">
                        {plugins.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-3 py-1 rounded text-sm ${link.active ? 'bg-teal-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}