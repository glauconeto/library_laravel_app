import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthCard from '@/Components/AuthCard';
import AuthCardLogo from '@/Components/AuthCardLogo';
import Input from '@/Components/Input';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';

interface ForgotPasswordFormData {
    email: string;
}

interface ForgotPasswordProps {
    status: string | null;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useForm<ForgotPasswordFormData>({
        email: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthCard>
            <Head title="Forgot Password" />

            <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Esqueceu sua senha? Sem problema. Basta nos informar seu endereço de e-mail que enviaremos um link para redefinição de senha.
            </div>

            {status && (
                <div className="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    {status}
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.email} />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton disabled={processing}>
                        Enviar Link de Redefinição de Senha
                    </PrimaryButton>
                </div>
            </form>
        </AuthCard>
    );
}