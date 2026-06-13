import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';

class ApiService {
  // 🔐 AMBIL TOKEN
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // 🔥 GET REPORTS
  static Future<List> getReports({String? search, String? type}) async {
    try {
      final token = await getToken();

      print("TOKEN DI API: $token");

      final uri = Uri.parse("${ApiConfig.baseUrl}/reports").replace(
        queryParameters: {
          if (search != null && search.trim().isNotEmpty)
            "search": search.trim(),
          if (type != null && type != "all") "type": type,
        },
      );

      final response = await http.get(
        uri,
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
      );

      print("STATUS: ${response.statusCode}");
      print("BODY: ${response.body}");

      // 🔥 HANDLE RESPONSE
      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);

        // pastikan struktur benar
        if (jsonData['data'] != null && jsonData['data']['data'] != null) {
          return jsonData['data']['data'];
        } else {
          throw Exception("Format data tidak sesuai");
        }
      }

      // 🔴 401 UNAUTHORIZED
      else if (response.statusCode == 401) {
        throw Exception("Unauthorized (token tidak valid / belum login)");
      }

      // 🔴 ERROR LAIN
      else {
        throw Exception("Gagal ambil data (${response.statusCode})");
      }
    } catch (e) {
      throw Exception("Error API: $e");
    }
  }

  // 🔍 GET DETAIL REPORT
  static Future<Map<String, dynamic>> getCurrentUser() async {
    try {
      final token = await getToken();

      final response = await http.get(
        Uri.parse("${ApiConfig.baseUrl}/me"),
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
      );

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return Map<String, dynamic>.from(jsonData['data']);
      }

      throw Exception("Gagal ambil profil");
    } catch (e) {
      throw Exception("Error profil: $e");
    }
  }

  static Future<Map<String, dynamic>> updateProfile({
    required Map<String, String> data,
    XFile? profilePhoto,
  }) async {
    try {
      final token = await getToken();
      final request = http.MultipartRequest(
        "POST",
        Uri.parse("${ApiConfig.baseUrl}/me"),
      );

      request.headers.addAll({
        "Authorization": "Bearer $token",
        "Accept": "application/json",
      });
      request.fields.addAll(data);

      if (profilePhoto != null) {
        request.files.add(
          http.MultipartFile.fromBytes(
            "profile_photo",
            await profilePhoto.readAsBytes(),
            filename: profilePhoto.name,
          ),
        );
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return Map<String, dynamic>.from(jsonData['data']);
      }

      var message = "Gagal update profil";
      try {
        final jsonData = json.decode(response.body);
        message = (jsonData['message'] ?? message).toString();
      } catch (_) {
        // Pakai pesan default saat response bukan JSON.
      }
      throw Exception(message);
    } catch (e) {
      if (e is Exception) {
        rethrow;
      }

      throw Exception("Error update profil: $e");
    }
  }

  static Future<List> getMyReports({
    String? search,
    String? filter,
  }) async {
    try {
      final token = await getToken();
      final query = <String, String>{
        if (search != null && search.trim().isNotEmpty)
          "search": search.trim(),
        if (filter == "Selesai") "status": "selesai",
        if (filter == "Belum Selesai") "status": "belum_selesai",
        if (filter == "Lihat Semua") "filter": "all",
        if (filter == "Dicari" || filter == "Ditemukan") "type": filter!,
      };

      final uri = Uri.parse("${ApiConfig.baseUrl}/my-reports").replace(
        queryParameters: query,
      );

      final response = await http.get(
        uri,
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
      );

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return jsonData['data'];
      }

      throw Exception("Gagal ambil laporan saya");
    } catch (e) {
      throw Exception("Error laporan saya: $e");
    }
  }

  static Future<void> updateReportStatus(String id, String status) async {
    await updateReport(id, {"status": status});
  }

  static Future<Map<String, dynamic>> getReportDetail(String id) async {
    try {
      final token = await getToken();

      final response = await http.get(
        Uri.parse("${ApiConfig.baseUrl}/reports/$id"),
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
      );

      print("DETAIL STATUS: ${response.statusCode}");
      print("DETAIL BODY: ${response.body}");

      if (response.statusCode == 200) {
        final jsonData = json.decode(response.body);
        return jsonData['data'];
      } else {
        throw Exception("Gagal ambil detail");
      }
    } catch (e) {
      throw Exception("Error detail: $e");
    }
  }

  // ➕ CREATE REPORT
  static Future<void> createReport(
    Map<String, String> data,
    List<XFile> images,
  ) async {
    try {
      final token = await getToken();
      final request = http.MultipartRequest(
        "POST",
        Uri.parse("${ApiConfig.baseUrl}/reports"),
      );

      request.headers.addAll({
        "Authorization": "Bearer $token",
        "Accept": "application/json",
      });
      request.fields.addAll(data);

      for (final image in images) {
        request.files.add(
          http.MultipartFile.fromBytes(
            "images[]",
            await image.readAsBytes(),
            filename: image.name,
          ),
        );
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      print("CREATE STATUS: ${response.statusCode}");
      print("CREATE BODY: ${response.body}");

      if (response.statusCode != 200 && response.statusCode != 201) {
        var message = "Gagal tambah data";
        try {
          final jsonData = json.decode(response.body);
          message = (jsonData['message'] ?? message).toString();
        } catch (_) {
          // Biarkan pesan default saat response bukan JSON.
        }
        throw Exception(message);
      }
    } catch (e) {
      if (e is Exception) {
        rethrow;
      }

      throw Exception("Error create: $e");
    }
  }

  // ✏️ UPDATE REPORT
  static Future<void> updateReport(String id, Map<String, dynamic> data) async {
    try {
      final token = await getToken();

      final response = await http.put(
        Uri.parse("${ApiConfig.baseUrl}/reports/$id"),
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
        body: data,
      );

      print("UPDATE STATUS: ${response.statusCode}");

      if (response.statusCode != 200) {
        throw Exception("Gagal update data");
      }
    } catch (e) {
      throw Exception("Error update: $e");
    }
  }

  // 🗑 DELETE REPORT
  static Future<void> deleteReport(String id) async {
    try {
      final token = await getToken();

      final response = await http.delete(
        Uri.parse("${ApiConfig.baseUrl}/reports/$id"),
        headers: {
          "Authorization": "Bearer $token",
          "Accept": "application/json",
        },
      );

      print("DELETE STATUS: ${response.statusCode}");

      if (response.statusCode != 200) {
        throw Exception("Gagal hapus data");
      }
    } catch (e) {
      throw Exception("Error delete: $e");
    }
  }
}
