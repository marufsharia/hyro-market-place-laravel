import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Navbar from '@/Components/Navbar';

export default function Index({ categories, docs, versions, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedVersion, setSelectedVersion] = useState(filters.version || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('docs.index'), { 
            search, 
            version: selectedVersion,
            category: filters.category 
        }, { 
            preserveState: true 
        });
    };

    const handleCategoryFilter = (categorySlug) => {
        router.get(route('docs.index'), { 
            search, 
            version: selectedVersion,
            category: categorySlug 
        }, { 
            preserveState: true 
        });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedVersion('');
        router.get(route('docs.index'));
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900">
            <Head title="Documentation - Hyro Market" />
            <Navbar />

            <div className="max-w-7xl mx-auto px-4 py-8">
                {/* Header */}
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                        📚 Documentation
                    </h1>
                    <p className="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Everything you need to know about Hyro plugins, development, and best practices
                    </p>
                </div>

                {/* Search Bar */}
                <div className="max-w-3xl mx-auto mb-8">
                    <form onSubmit={handleSearch} className="flex gap-3">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search documentation..."
                            className="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-800 dark:text-white"
                        />
                        <select
                            value={selectedVersion}
                            onChange={(e) => setSelectedVersion(e.target.value)}
                            className="px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-800 dark:text-white"
                        >
                            <option value="">All Versions</option>
                            {versions.map(version => (
                                <option key={version} value={version}>v{version}</option>
                            ))}
                        </select>
                        <button
                            type="submit"
                            className="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors"
                        >
                            Search
                        </button>
                    </form>
                    {(filters.search || filters.category || filters.version) && (
                        <button
                            onClick={clearFilters}
                            className="mt-3 text-sm text-teal-600 hover:underline"
                        >
                            Clear filters
                        </button>
                    )}
                </div>

                {/* Categories Grid */}
                {!filters.search && !filters.category && (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                        {categories.map(category => (
                            <button
                                key={category.id}
                                onClick={() => handleCategoryFilter(category.slug)}
                                className="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-lg hover:shadow-xl transition-shadow text-left"
                            >
                                <div className="text-4xl mb-3">{category.icon || '📄'}</div>
                                <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-2">
                                    {category.name}
                                </h3>
                                <p className="text-sm text-slate-600 dark:text-slate-400 mb-3">
                                    {category.description}
                                </p>
                                <div className="text-sm text-teal-600 dark:text-teal-400">
                                    {category.published_documentations?.length || 0} articles →
                                </div>
                            </button>
                        ))}
                    </div>
                )}

                {/* Documentation List */}
                {(filters.search || filters.category) && (
                    <div className="space-y-4">
                        <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                            {filters.category ? 
                                `${categories.find(c => c.slug === filters.category)?.name || 'Category'} Articles` : 
                                'Search Results'
                            }
                        </h2>
                        
                        {docs.data && docs.data.length > 0 ? (
                            <>
                                {docs.data.map(doc => (
                                    <Link
                                        key={doc.id}
                                        href={route('docs.show', doc.slug)}
                                        className="block bg-white dark:bg-slate-800 p-6 rounded-xl shadow hover:shadow-lg transition-shadow"
                                    >
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3 mb-2">
                                                    <h3 className="text-xl font-bold text-slate-900 dark:text-white">
                                                        {doc.title}
                                                    </h3>
                                                    <span className="text-xs bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-slate-600 dark:text-slate-400">
                                                        v{doc.version}
                                                    </span>
                                                </div>
                                                {doc.excerpt && (
                                                    <p className="text-slate-600 dark:text-slate-400 mb-3">
                                                        {doc.excerpt}
                                                    </p>
                                                )}
                                                <div className="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                                                    <span>{doc.category?.name}</span>
                                                    <span>•</span>
                                                    <span>{doc.views} views</span>
                                                </div>
                                            </div>
                                            <div className="text-teal-600 dark:text-teal-400">
                                                →
                                            </div>
                                        </div>
                                    </Link>
                                ))}

                                {/* Pagination */}
                                {docs.links && docs.links.length > 3 && (
                                    <div className="mt-6 flex justify-center gap-2">
                                        {docs.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url || '#'}
                                                preserveScroll
                                                className={`px-3 py-1 rounded text-sm ${
                                                    link.active 
                                                        ? 'bg-teal-600 text-white' 
                                                        : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
                                                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="text-center py-12 bg-white dark:bg-slate-800 rounded-xl">
                                <p className="text-slate-500 dark:text-slate-400">
                                    No documentation found. Try adjusting your search.
                                </p>
                            </div>
                        )}
                    </div>
                )}

                {/* Popular Articles */}
                {!filters.search && !filters.category && (
                    <div className="mt-12">
                        <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                            📖 All Documentation
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {docs.data && docs.data.map(doc => (
                                <Link
                                    key={doc.id}
                                    href={route('docs.show', doc.slug)}
                                    className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow flex items-center justify-between"
                                >
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white mb-1">
                                            {doc.title}
                                        </h4>
                                        <p className="text-sm text-slate-500 dark:text-slate-400">
                                            {doc.category?.name}
                                        </p>
                                    </div>
                                    <span className="text-teal-600 dark:text-teal-400">→</span>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
