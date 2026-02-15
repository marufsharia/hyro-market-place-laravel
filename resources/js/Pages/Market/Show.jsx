import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Navbar from '@/Components/Navbar';

export default function Show({ plugin, userReview, relatedPlugins, auth }) {
    const { flash } = usePage().props;
    const [isFavorited, setIsFavorited] = useState(plugin.is_favorited || false);
    const [favoriteLoading, setFavoriteLoading] = useState(false);
    const [selectedRating, setSelectedRating] = useState(userReview?.rating || 0);
    const [hoverRating, setHoverRating] = useState(0);
    const [showReportModal, setShowReportModal] = useState(false);
    const [activeTab, setActiveTab] = useState('overview');
    const [selectedScreenshot, setSelectedScreenshot] = useState(null);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        rating: userReview?.rating || '',
        comment: userReview?.comment || '',
    });

    const reportForm = useForm({
        reason: '',
        description: '',
    });

    const handleDownload = () => {
        router.post(route('market.download', plugin.slug));
    };

    const handleFavoriteToggle = async () => {
        if (!auth.user) {
            router.visit(route('login'));
            return;
        }

        setFavoriteLoading(true);
        
        router.post(
            route('favorites.toggle', plugin.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsFavorited(!isFavorited);
                },
                onFinish: () => {
                    setFavoriteLoading(false);
                },
            }
        );
    };

    const handleReviewSubmit = (e) => {
        e.preventDefault();
        
        if (!auth.user) {
            router.visit(route('login'));
            return;
        }

        post(route('reviews.store', plugin.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (!userReview) {
                    reset();
                    setSelectedRating(0);
                }
            },
        });
    };

    const handleReportSubmit = (e) => {
        e.preventDefault();
        
        reportForm.post(route('reports.store', plugin.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowReportModal(false);
                reportForm.reset();
            },
        });
    };

    const sharePlugin = (platform) => {
        const url = window.location.href;
        const text = `Check out ${plugin.name} on Hyro Market`;
        
        const shareUrls = {
            twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
            linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`,
            copy: url
        };

        if (platform === 'copy') {
            navigator.clipboard.writeText(url);
            alert('Link copied to clipboard!');
        } else {
            window.open(shareUrls[platform], '_blank', 'width=600,height=400');
        }
    };

    const renderStars = (rating) => {
        return (
            <span className="text-amber-500">
                {'★'.repeat(Math.floor(rating))}
                {'☆'.repeat(5 - Math.floor(rating))}
            </span>
        );
    };

    const renderRatingSelector = () => {
        return (
            <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <button
                        key={star}
                        type="button"
                        onClick={() => {
                            setSelectedRating(star);
                            setData('rating', star);
                        }}
                        onMouseEnter={() => setHoverRating(star)}
                        onMouseLeave={() => setHoverRating(0)}
                        className="text-3xl focus:outline-none transition-colors"
                    >
                        <span className={
                            star <= (hoverRating || selectedRating || data.rating)
                                ? 'text-amber-500'
                                : 'text-slate-300 dark:text-slate-600'
                        }>
                            ★
                        </span>
                    </button>
                ))}
            </div>
        );
    };

    const canReview = auth.user && plugin.user_id !== auth.user.id;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-900 font-sans">
            <Head title={`${plugin.name} - Hyro Market`} />
            <Navbar />

            <div className="max-w-6xl mx-auto px-4 py-8">
                <Link href={route('market.index')} className="text-teal-600 hover:underline text-sm mb-4 inline-block">
                    ← Back to Marketplace
                </Link>

                {flash?.success && (
                    <div className="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg">
                        {flash.error}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">

                        {/* Plugin Header */}
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
                            <div className="p-8 flex flex-col md:flex-row gap-6">
                                <img 
                                    src={plugin.logo_path ? `/storage/${plugin.logo_path}` : '/images/default-plugin.png'} 
                                    alt={plugin.name}
                                    className="w-32 h-32 rounded-xl object-cover shadow-sm" 
                                    onError={(e) => { e.target.src = '/images/default-plugin.png'; }}
                                />
                                <div className="flex-1">
                                    <div className="flex items-start justify-between gap-4 mb-3">
                                        <div>
                                            <h1 className="text-3xl font-bold text-slate-900 dark:text-white mb-2">{plugin.name}</h1>
                                            <div className="flex items-center gap-2">
                                                {renderStars(plugin.rating_avg || 0)}
                                                <span className="text-sm text-slate-600 dark:text-slate-400">
                                                    {plugin.rating_avg ? plugin.rating_avg.toFixed(1) : '0.0'} ({plugin.rating_count || 0} {plugin.rating_count === 1 ? 'review' : 'reviews'})
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            {auth.user && (
                                                <button
                                                    onClick={handleFavoriteToggle}
                                                    disabled={favoriteLoading}
                                                    className={`p-2 rounded-full transition-colors ${
                                                        favoriteLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100 dark:hover:bg-slate-700'
                                                    }`}
                                                    title={isFavorited ? 'Remove from favorites' : 'Add to favorites'}
                                                >
                                                    <svg 
                                                        className={`w-6 h-6 ${isFavorited ? 'fill-red-500 text-red-500' : 'fill-none text-slate-400'}`}
                                                        stroke="currentColor" 
                                                        strokeWidth="2" 
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                                    </svg>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                    <p className="text-slate-600 dark:text-slate-300 mb-4">{plugin.description}</p>
                                    
                                    <div className="flex flex-wrap gap-2 text-sm">
                                        <span className="bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full text-slate-800 dark:text-slate-200">
                                            v{plugin.version}
                                        </span>
                                        <span className="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full">
                                            {plugin.license_type}
                                        </span>
                                        <span className="bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-3 py-1 rounded-full">
                                            {plugin.compatibility}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Tabs */}
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
                            <div className="border-b border-slate-200 dark:border-slate-700">
                                <div className="flex overflow-x-auto">
                                    {['overview', 'screenshots', 'changelog', 'installation'].map((tab) => (
                                        <button
                                            key={tab}
                                            onClick={() => setActiveTab(tab)}
                                            className={`px-6 py-4 font-medium capitalize whitespace-nowrap transition-colors ${
                                                activeTab === tab
                                                    ? 'text-teal-600 border-b-2 border-teal-600'
                                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                            }`}
                                        >
                                            {tab}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div className="p-8">
                                {activeTab === 'overview' && (
                                    <div className="space-y-6">
                                        {plugin.requirements && (
                                            <div>
                                                <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-3">Requirements</h3>
                                                <div className="grid grid-cols-2 gap-4 text-sm">
                                                    {Object.entries(plugin.requirements).map(([key, value]) => (
                                                        <div key={key}>
                                                            <span className="text-slate-600 dark:text-slate-400 capitalize">{key}:</span>{' '}
                                                            <span className="text-slate-900 dark:text-white font-medium">{value}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                        
                                        <div className="text-sm text-slate-600 dark:text-slate-400">
                                            <p>Category: <span className="font-medium text-slate-900 dark:text-white">{plugin.category?.name}</span></p>
                                            <p className="mt-1">Downloads: <span className="font-bold text-slate-900 dark:text-white">{plugin.downloads}</span></p>
                                        </div>
                                    </div>
                                )}

                                {activeTab === 'screenshots' && (
                                    <div>
                                        {plugin.screenshots && plugin.screenshots.length > 0 ? (
                                            <div className="grid grid-cols-2 gap-4">
                                                {plugin.screenshots.map((screenshot, index) => (
                                                    <img
                                                        key={index}
                                                        src={`/storage/${screenshot}`}
                                                        alt={`Screenshot ${index + 1}`}
                                                        className="rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                                        onClick={() => setSelectedScreenshot(screenshot)}
                                                    />
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-slate-500 dark:text-slate-400 text-center py-8">No screenshots available</p>
                                        )}
                                    </div>
                                )}

                                {activeTab === 'changelog' && (
                                    <div>
                                        {plugin.changelog && plugin.changelog.length > 0 ? (
                                            <div className="space-y-4">
                                                {plugin.changelog.map((entry, index) => (
                                                    <div key={index} className="border-l-4 border-teal-500 pl-4">
                                                        <div className="flex items-center gap-3 mb-2">
                                                            <span className="font-bold text-slate-900 dark:text-white">{entry.version}</span>
                                                            <span className="text-sm text-slate-500 dark:text-slate-400">{entry.date}</span>
                                                        </div>
                                                        <ul className="list-disc list-inside text-sm text-slate-600 dark:text-slate-300 space-y-1">
                                                            {entry.changes.map((change, i) => (
                                                                <li key={i}>{change}</li>
                                                            ))}
                                                        </ul>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-slate-500 dark:text-slate-400 text-center py-8">No changelog available</p>
                                        )}
                                    </div>
                                )}

                                {activeTab === 'installation' && (
                                    <div>
                                        {plugin.installation_instructions ? (
                                            <div className="prose dark:prose-invert max-w-none">
                                                <pre className="bg-slate-100 dark:bg-slate-900 p-4 rounded-lg overflow-x-auto text-sm">
                                                    {plugin.installation_instructions}
                                                </pre>
                                            </div>
                                        ) : (
                                            <p className="text-slate-500 dark:text-slate-400 text-center py-8">No installation instructions available</p>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Review Form */}
                        {canReview && (
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8">
                                <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-4">
                                    {userReview ? 'Update Your Review' : 'Write a Review'}
                                </h2>
                                <form onSubmit={handleReviewSubmit}>
                                    <div className="mb-4">
                                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Rating <span className="text-red-500">*</span>
                                        </label>
                                        {renderRatingSelector()}
                                        {errors.rating && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.rating}</p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label htmlFor="comment" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            Comment (optional)
                                        </label>
                                        <textarea
                                            id="comment"
                                            value={data.comment}
                                            onChange={(e) => setData('comment', e.target.value)}
                                            maxLength={1000}
                                            rows={4}
                                            className="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                            placeholder="Share your experience with this plugin..."
                                        />
                                        <div className="mt-1 flex justify-between text-sm">
                                            <div>
                                                {errors.comment && (
                                                    <p className="text-red-600 dark:text-red-400">{errors.comment}</p>
                                                )}
                                            </div>
                                            <span className="text-slate-500 dark:text-slate-400">
                                                {data.comment.length}/1000
                                            </span>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing || !data.rating}
                                        className="bg-teal-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-teal-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {processing ? 'Submitting...' : (userReview ? 'Update Review' : 'Submit Review')}
                                    </button>
                                </form>
                            </div>
                        )}

                        {!auth.user && (
                            <div className="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-center">
                                <p className="text-blue-800 dark:text-blue-200">
                                    <Link href={route('login')} className="font-medium underline hover:no-underline">
                                        Sign in
                                    </Link>
                                    {' '}to write a review or favorite this plugin.
                                </p>
                            </div>
                        )}

                        {/* Reviews List */}
                        <div>
                            <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-4">
                                Reviews {plugin.rating_count > 0 && `(${plugin.rating_count})`}
                            </h2>
                            {plugin.reviews && plugin.reviews.data && plugin.reviews.data.length > 0 ? (
                                <>
                                    <div className="space-y-4">
                                        {plugin.reviews.data.map(review => (
                                            <div key={review.id} className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                                                <div className="flex justify-between items-start mb-3">
                                                    <div>
                                                        <span className="font-bold text-slate-900 dark:text-white">{review.user.name}</span>
                                                        <div className="mt-1">{renderStars(review.rating)}</div>
                                                    </div>
                                                    <span className="text-sm text-slate-500 dark:text-slate-400">
                                                        {new Date(review.created_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                {review.comment && (
                                                    <p className="text-slate-600 dark:text-slate-300 text-sm whitespace-pre-wrap">
                                                        {review.comment}
                                                    </p>
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    {plugin.reviews.links && plugin.reviews.links.length > 3 && (
                                        <div className="mt-6 flex justify-center gap-2">
                                            {plugin.reviews.links.map((link, index) => (
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
                                <p className="text-slate-500 dark:text-slate-400 text-center py-8 bg-white dark:bg-slate-800 rounded-lg">
                                    No reviews yet. Be the first to review this plugin!
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="lg:col-span-1 space-y-6">
                        {/* Download Card */}
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 sticky top-4">
                            <button 
                                onClick={handleDownload}
                                className="w-full bg-slate-900 dark:bg-teal-600 text-white px-6 py-3 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center justify-center gap-2 mb-4"
                            >
                                <span>↓</span> Download / Install
                            </button>
                            
                            <div className="text-sm text-slate-600 dark:text-slate-400 space-y-2 mb-4">
                                <p>Version: <span className="font-medium text-slate-900 dark:text-white">{plugin.version}</span></p>
                                <p>Downloads: <span className="font-bold text-slate-900 dark:text-white">{plugin.downloads}</span></p>
                                <p>License: <span className="font-medium text-slate-900 dark:text-white">{plugin.license_type}</span></p>
                            </div>

                            {/* Links */}
                            <div className="space-y-2 border-t border-slate-200 dark:border-slate-700 pt-4">
                                {plugin.documentation_url && (
                                    <a 
                                        href={plugin.documentation_url} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-2 text-sm text-teal-600 hover:underline"
                                    >
                                        📚 Documentation
                                    </a>
                                )}
                                {plugin.support_url && (
                                    <a 
                                        href={plugin.support_url} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-2 text-sm text-teal-600 hover:underline"
                                    >
                                        💬 Support
                                    </a>
                                )}
                                {plugin.demo_url && (
                                    <a 
                                        href={plugin.demo_url} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-2 text-sm text-teal-600 hover:underline"
                                    >
                                        🎮 Live Demo
                                    </a>
                                )}
                                {plugin.repository_url && (
                                    <a 
                                        href={plugin.repository_url} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-2 text-sm text-teal-600 hover:underline"
                                    >
                                        💻 Source Code
                                    </a>
                                )}
                            </div>

                            {/* Share Buttons */}
                            <div className="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                                <p className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Share</p>
                                <div className="flex gap-2">
                                    <button onClick={() => sharePlugin('twitter')} className="p-2 bg-slate-100 dark:bg-slate-700 rounded hover:bg-slate-200 dark:hover:bg-slate-600" title="Share on Twitter">
                                        𝕏
                                    </button>
                                    <button onClick={() => sharePlugin('facebook')} className="p-2 bg-slate-100 dark:bg-slate-700 rounded hover:bg-slate-200 dark:hover:bg-slate-600" title="Share on Facebook">
                                        f
                                    </button>
                                    <button onClick={() => sharePlugin('linkedin')} className="p-2 bg-slate-100 dark:bg-slate-700 rounded hover:bg-slate-200 dark:hover:bg-slate-600" title="Share on LinkedIn">
                                        in
                                    </button>
                                    <button onClick={() => sharePlugin('copy')} className="p-2 bg-slate-100 dark:bg-slate-700 rounded hover:bg-slate-200 dark:hover:bg-slate-600" title="Copy Link">
                                        🔗
                                    </button>
                                </div>
                            </div>

                            {/* Report Button */}
                            {auth.user && auth.user.id !== plugin.user_id && (
                                <button
                                    onClick={() => setShowReportModal(true)}
                                    className="w-full mt-4 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    🚩 Report Plugin
                                </button>
                            )}
                        </div>

                        {/* Author Card */}
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                            <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-4">Author</h3>
                            <div className="flex items-center gap-3 mb-3">
                                <div className="w-12 h-12 bg-teal-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {plugin.user?.name?.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <p className="font-medium text-slate-900 dark:text-white">{plugin.user?.name}</p>
                                    <p className="text-sm text-slate-500 dark:text-slate-400">{plugin.user?.email}</p>
                                </div>
                            </div>
                        </div>

                        {/* Related Plugins */}
                        {relatedPlugins && relatedPlugins.length > 0 && (
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
                                <h3 className="text-lg font-bold text-slate-900 dark:text-white mb-4">Related Plugins</h3>
                                <div className="space-y-3">
                                    {relatedPlugins.map((related) => (
                                        <Link
                                            key={related.id}
                                            href={route('market.show', related.slug)}
                                            className="flex gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        >
                                            <img
                                                src={related.logo_path ? `/storage/${related.logo_path}` : '/images/default-plugin.png'}
                                                alt={related.name}
                                                className="w-12 h-12 rounded object-cover"
                                                onError={(e) => { e.target.src = '/images/default-plugin.png'; }}
                                            />
                                            <div className="flex-1 min-w-0">
                                                <p className="font-medium text-slate-900 dark:text-white text-sm truncate">{related.name}</p>
                                                <div className="flex items-center gap-1 text-xs">
                                                    {renderStars(related.rating_avg || 0)}
                                                    <span className="text-slate-500 dark:text-slate-400">
                                                        {related.rating_avg ? related.rating_avg.toFixed(1) : '0.0'}
                                                    </span>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Report Modal */}
            {showReportModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                        <div className="flex justify-between items-center mb-4">
                            <h3 className="text-xl font-bold text-slate-900 dark:text-white">Report Plugin</h3>
                            <button
                                onClick={() => setShowReportModal(false)}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleReportSubmit}>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Reason <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={reportForm.data.reason}
                                    onChange={(e) => reportForm.setData('reason', e.target.value)}
                                    className="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                    required
                                >
                                    <option value="">Select a reason</option>
                                    <option value="spam">Spam</option>
                                    <option value="inappropriate">Inappropriate Content</option>
                                    <option value="broken">Broken/Not Working</option>
                                    <option value="copyright">Copyright Violation</option>
                                    <option value="security">Security Issue</option>
                                    <option value="other">Other</option>
                                </select>
                                {reportForm.errors.reason && (
                                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{reportForm.errors.reason}</p>
                                )}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Description (optional)
                                </label>
                                <textarea
                                    value={reportForm.data.description}
                                    onChange={(e) => reportForm.setData('description', e.target.value)}
                                    maxLength={1000}
                                    rows={4}
                                    className="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                    placeholder="Provide additional details..."
                                />
                                {reportForm.errors.description && (
                                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{reportForm.errors.description}</p>
                                )}
                            </div>

                            <div className="flex gap-3">
                                <button
                                    type="button"
                                    onClick={() => setShowReportModal(false)}
                                    className="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={reportForm.processing || !reportForm.data.reason}
                                    className="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {reportForm.processing ? 'Submitting...' : 'Submit Report'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Screenshot Modal */}
            {selectedScreenshot && (
                <div 
                    className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4"
                    onClick={() => setSelectedScreenshot(null)}
                >
                    <img
                        src={`/storage/${selectedScreenshot}`}
                        alt="Screenshot"
                        className="max-w-full max-h-full rounded-lg"
                        onClick={(e) => e.stopPropagation()}
                    />
                    <button
                        onClick={() => setSelectedScreenshot(null)}
                        className="absolute top-4 right-4 text-white text-4xl hover:text-slate-300"
                    >
                        ✕
                    </button>
                </div>
            )}
        </div>
    );
}
