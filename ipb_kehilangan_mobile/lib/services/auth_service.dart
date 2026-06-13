import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';

class OtpChallenge {
  const OtpChallenge({
    required this.email,
    required this.purpose,
    required this.message,
    required this.registrationToken,
  });

  final String email;
  final String purpose;
  final String message;
  final String registrationToken;
}

class AuthService {
  static Future<String> login(String email, String password) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/login"),
      headers: {"Accept": "application/json"},
      body: {
        "login": email,
        "password": password,
      },
    );

    final data = _decode(response.body);

    if (response.statusCode == 200 && data['token'] != null) {
      return data['token'].toString();
    }

    throw Exception(_messageFromResponse(data, "Login gagal"));
  }

  static Future<OtpChallenge> register(
    String name,
    String email,
    String phone,
    String nim,
    String password,
  ) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/register"),
      headers: {"Accept": "application/json"},
      body: {
        "name": name,
        "email": email,
        "phone": phone,
        "nim": nim,
        "password": password,
      },
    );

    final data = _decode(response.body);

    if ((response.statusCode == 200 || response.statusCode == 201) &&
        data['otp_required'] == true) {
      return OtpChallenge(
        email: data['email'].toString(),
        purpose: data['purpose'].toString(),
        message: data['message']?.toString() ?? "Kode OTP sudah dikirim",
        registrationToken: data['registration_token'].toString(),
      );
    }

    throw Exception(_messageFromResponse(data, "Register gagal"));
  }

  static Future<String> verifyOtp(
    String email,
    String otp,
    String purpose,
    String registrationToken,
  ) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/verify-otp"),
      headers: {"Accept": "application/json"},
      body: {
        "email": email,
        "otp": otp,
        "purpose": purpose,
        "registration_token": registrationToken,
      },
    );

    final data = _decode(response.body);

    if (response.statusCode == 200 && data['token'] != null) {
      return data['token'].toString();
    }

    throw Exception(_messageFromResponse(data, "Verifikasi OTP gagal"));
  }

  static Future<String?> resendOtp(
    String email,
    String purpose, {
    String? registrationToken,
  }) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/resend-otp"),
      headers: {"Accept": "application/json"},
      body: {
        "email": email,
        "purpose": purpose,
        if (registrationToken != null) "registration_token": registrationToken,
      },
    );

    final data = _decode(response.body);

    if (response.statusCode != 200) {
      throw Exception(_messageFromResponse(data, "Kirim ulang OTP gagal"));
    }

    return data is Map && data['registration_token'] != null
        ? data['registration_token'].toString()
        : null;
  }

  static Future<void> forgotPassword(String email) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/forgot-password"),
      headers: {"Accept": "application/json"},
      body: {"email": email},
    );

    final data = _decode(response.body);

    if (response.statusCode != 200) {
      throw Exception(_messageFromResponse(data, "Kirim OTP reset gagal"));
    }
  }

  static Future<void> resetPassword(
    String email,
    String otp,
    String password,
    String passwordConfirmation,
  ) async {
    final response = await http.post(
      Uri.parse("${ApiConfig.baseUrl}/reset-password"),
      headers: {"Accept": "application/json"},
      body: {
        "email": email,
        "otp": otp,
        "password": password,
        "password_confirmation": passwordConfirmation,
      },
    );

    final data = _decode(response.body);

    if (response.statusCode != 200) {
      throw Exception(_messageFromResponse(data, "Reset password gagal"));
    }
  }

  static dynamic _decode(String body) {
    try {
      return json.decode(body);
    } catch (_) {
      return {};
    }
  }

  static String _messageFromResponse(dynamic data, String fallback) {
    if (data is Map && data['errors'] is Map) {
      final errors = data['errors'] as Map;
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
      }
    }

    if (data is Map && data['message'] != null) {
      return data['message'].toString();
    }

    return fallback;
  }
}
