import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthCard from '../../components/AuthCard';
import AuthCardLogo from '../../components/AuthCardLogo';
import Input from '../../components/Input';
import InputError from '../../components/InputError';
import InputLabel from '../../components/InputLabel';
import PrimaryButton from '../../components/PrimaryButton';

export default function TwoFactorChallenge() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        recovery_code: '',
    });

    const [useRecoveryCode, setUseRecoveryCode] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('two-factor.login'));
    };

    return (
        <AuthCard>
            <Head title="Two-Factor Challenge" />

            <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {useRecoveryCode
                    ? 'Please enter your recovery code to continue.'
                    : 'Please enter the 6-digit code from your authenticator app.'}
            </div>

            <form onSubmit={handleSubmit}>
                {!useRecoveryCode && (
                    <div>
                        <InputLabel htmlFor="code" value="Code" />
                        <Input
                            id="code"
                            type="text"
                            name="code"
                            value={data.code}
                            className="mt-1 block w-full"
                            autoFocus
                            onChange={(e) => setData('code', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.code} />
                    </div>
                )}

                {useRecoveryCode && (
                    <div>
                        <InputLabel htmlFor="recovery_code" value="Recovery Code" />
                        <Input
                            id="recovery_code"
                            type="text"
                            name="recovery_code"
                            value={data.recovery_code}
                            className="mt-1 block w-full"
                            autoFocus
                            onChange={(e) => setData('recovery_code', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.recovery_code} />
                    </div>
                )}

                <div className="mt-4">
                    <button
                        type="button"
                        className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                        onClick={() => setUseRecoveryCode(!useRecoveryCode)}
                    >
                        {useRecoveryCode ? 'Use an authentication code' : 'Use a recovery code'}
                    </button>
                </div>

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton disabled={processing}>
                        Login
                    </PrimaryButton>
                </div>
            </form>
        </AuthCard>
    );
}