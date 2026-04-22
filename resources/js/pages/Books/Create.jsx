import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../layouts/AppLayout';
import Input from '../../Components/Input';
import PrimaryButton from '../../Components/PrimaryButton';

export default function BooksCreate({ genres }) {
    const [data, setData] = useState({
        title: '',
        author: '',
        genre: '',
        year: new Date().getFullYear(),
        isbn: '',
        stock: 1,
        description: '',
    });

    const [errors, setErrors] = useState({});
    const [processing, setProcessing] = useState(false);

    const handleChange = (e) => {
        const { name, value, type } = e.target;
        
        setData(prev => ({
            ...prev,
            [name]: type === 'number' ? parseInt(value) || 0 : value
        }));
        
        // Clear error for this field when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);
        
        router.post(route('books.store'), data, {
            onSuccess: () => {
                router.visit(route('books.index'));
            },
            onError: (errors) => {
                setErrors(errors);
                setProcessing(false);
            },
            onFinish: () => {
                setProcessing(false);
            }
        });
    };

    return (
        <AppLayout>
            <Head title="Adicionar Novo Livro" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <form onSubmit={handleSubmit}>
                            <div className="p-6 space-y-6">
                                <div className="flex justify-between items-center mb-6">
                                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                                        Adicionar Novo Livro
                                    </h1>
                                    <Link href={route('books.index')}>
                                        <button
                                            type="button"
                                            className="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition duration-150"
                                        >
                                            Cancelar
                                        </button>
                                    </Link>
                                </div>

                                {/* Title */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Título
                                    </label>
                                    <Input
                                        name="title"
                                        value={data.title}
                                        onChange={handleChange}
                                        className="w-full"
                                        required
                                    />
                                    {errors.title && (
                                        <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                            {errors.title}
                                        </p>
                                    )}
                                </div>

                                {/* Author */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Autor
                                    </label>
                                    <Input
                                        name="author"
                                        value={data.author}
                                        onChange={handleChange}
                                        className="w-full"
                                        required
                                    />
                                    {errors.author && (
                                        <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                            {errors.author}
                                        </p>
                                    )}
                                </div>

                                {/* Genre and Year */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Gênero
                                        </label>
                                        <select
                                            name="genre"
                                            value={data.genre}
                                            onChange={handleChange}
                                            className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="">Selecione um gênero</option>
                                            {genres.map((genre) => (
                                                <option key={genre} value={genre}>
                                                    {genre}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.genre && (
                                            <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                                {errors.genre}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Ano
                                        </label>
                                        <Input
                                            name="year"
                                            type="number"
                                            value={data.year}
                                            onChange={handleChange}
                                            min="1000"
                                            max={new Date().getFullYear()}
                                            className="w-full"
                                            required
                                        />
                                        {errors.year && (
                                            <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                                {errors.year}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* ISBN and Stock */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            ISBN
                                        </label>
                                        <Input
                                            name="isbn"
                                            value={data.isbn}
                                            onChange={handleChange}
                                            placeholder="978-0-0000-0000-0"
                                            className="w-full"
                                            required
                                        />
                                        {errors.isbn && (
                                            <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                                {errors.isbn}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Quantidade em Estoque
                                        </label>
                                        <Input
                                            name="stock"
                                            type="number"
                                            value={data.stock}
                                            onChange={handleChange}
                                            min="1"
                                            className="w-full"
                                            required
                                        />
                                        {errors.stock && (
                                            <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                                {errors.stock}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {/* Description */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Descrição
                                    </label>
                                    <textarea
                                        name="description"
                                        value={data.description}
                                        onChange={handleChange}
                                        rows={6}
                                        placeholder="Digite uma breve descrição do livro..."
                                        className="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    />
                                    {errors.description && (
                                        <p className="mt-2 text-sm text-red-600 dark:text-red-400">
                                            {errors.description}
                                        </p>
                                    )}
                                </div>

                                {/* Submit Button */}
                                <div className="flex justify-end">
                                    <PrimaryButton disabled={processing}>
                                        {processing ? 'Adicionando...' : 'Adicionar Livro'}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
