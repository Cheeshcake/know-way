
import json
import sys
import random

class ConnectFour:
    def __init__(self):
        
        self.rows = 6
        self.columns = 7
        self.board = [[0 for _ in range(self.columns)] for _ in range(self.rows)]
        self.player = 1
        self.ai = 2
        
    def drop_piece(self, col, piece):
        """Drops a piece in the specified column"""
        for row in range(self.rows-1, -1, -1):
            if self.board[row][col] == 0:
                self.board[row][col] = piece
                return row
        return -1  
    
    def is_valid_location(self, col):
        """Check if a column is valid for a move"""
        return self.board[0][col] == 0
    
    def get_valid_locations(self):
        """Returns all valid column indices for moves"""
        return [col for col in range(self.columns) if self.is_valid_location(col)]
    
    def remove_piece(self, row, col):
        """Removes a piece from the board"""
        self.board[row][col] = 0
    
    def check_win(self, piece):
        """Check if the given piece has won"""
        for row in range(self.rows):
            for col in range(self.columns - 3):
                if (self.board[row][col] == piece and 
                    self.board[row][col+1] == piece and 
                    self.board[row][col+2] == piece and 
                    self.board[row][col+3] == piece):
                    return True
        
        for row in range(self.rows - 3):
            for col in range(self.columns):
                if (self.board[row][col] == piece and 
                    self.board[row+1][col] == piece and 
                    self.board[row+2][col] == piece and 
                    self.board[row+3][col] == piece):
                    return True
        
        for row in range(self.rows - 3):
            for col in range(self.columns - 3):
                if (self.board[row][col] == piece and 
                    self.board[row+1][col+1] == piece and 
                    self.board[row+2][col+2] == piece and 
                    self.board[row+3][col+3] == piece):
                    return True
        
        for row in range(self.rows - 3):
            for col in range(3, self.columns):
                if (self.board[row][col] == piece and 
                    self.board[row+1][col-1] == piece and 
                    self.board[row+2][col-2] == piece and 
                    self.board[row+3][col-3] == piece):
                    return True
        
        return False
    
    def is_board_full(self):
        """Check if the board is full"""
        return len(self.get_valid_locations()) == 0
    
    def is_game_over(self):
        """Check if the game is over"""
        return self.check_win(self.player) or self.check_win(self.ai) or self.is_board_full()
    
    def count_pieces(self, window, piece):
        """Count occurrences of a piece in a window"""
        return window.count(piece)
    
    def evaluate_window(self, window, piece):
        """Evaluate a window of 4 positions"""
        opponent_piece = self.player if piece == self.ai else self.ai
        
        score = 0
        piece_count = self.count_pieces(window, piece)
        empty_count = self.count_pieces(window, 0)
        opponent_count = self.count_pieces(window, opponent_piece)
        
        if piece_count == 4:
            score += 100
        elif piece_count == 3 and empty_count == 1:
            score += 5
        elif piece_count == 2 and empty_count == 2:
            score += 2
        
        if opponent_count == 3 and empty_count == 1:
            score -= 4
        
        return score
    
    def get_column(self, col):
        """Get a column from the board"""
        return [self.board[row][col] for row in range(self.rows)]
    
    def get_row(self, row):
        """Get a row from the board"""
        return self.board[row]
    
    def score_position(self, piece):
        """Score the entire board position for the given piece"""
        score = 0
        
        center_col = self.get_column(self.columns // 2)
        center_count = center_col.count(piece)
        score += center_count * 3
        
        for row in range(self.rows):
            row_array = self.get_row(row)
            for col in range(self.columns - 3):
                window = row_array[col:col+4]
                score += self.evaluate_window(window, piece)
        
        for col in range(self.columns):
            col_array = self.get_column(col)
            for row in range(self.rows - 3):
                window = col_array[row:row+4]
                score += self.evaluate_window(window, piece)
        
        for row in range(self.rows - 3):
            for col in range(self.columns - 3):
                window = [self.board[row+i][col+i] for i in range(4)]
                score += self.evaluate_window(window, piece)
        
        for row in range(self.rows - 3):
            for col in range(3, self.columns):
                window = [self.board[row+i][col-i] for i in range(4)]
                score += self.evaluate_window(window, piece)
        
        return score
    
    def minimax(self, depth, is_maximizing):
        """Minimax algorithm for Connect Four"""
        if self.check_win(self.ai):
            return (None, 1000000)
        elif self.check_win(self.player):
            return (None, -1000000)
        elif self.is_board_full() or depth == 0:
            return (None, self.score_position(self.ai))
        
        valid_locations = self.get_valid_locations()
        if not valid_locations:
            return (None, 0)
            
        if is_maximizing:
            value = float('-inf')
            column = random.choice(valid_locations)
            for col in valid_locations:
                row = self.drop_piece(col, self.ai)
                if row != -1:  # Valid move
                    new_score = self.minimax(depth - 1, False)[1]
                    self.remove_piece(row, col)
                    if new_score > value:
                        value = new_score
                        column = col
            return column, value
        else:
            value = float('inf')
            column = random.choice(valid_locations)
            for col in valid_locations:
                row = self.drop_piece(col, self.player)
                if row != -1:  # Valid move
                    new_score = self.minimax(depth - 1, True)[1]
                    self.remove_piece(row, col)
                    if new_score < value:
                        value = new_score
                        column = col
            return column, value
    
    def alpha_beta(self, depth, alpha, beta, is_maximizing):
        """Alpha-Beta pruning algorithm for Connect Four"""
        if self.check_win(self.ai):
            return (None, 1000000)
        elif self.check_win(self.player):
            return (None, -1000000)
        elif self.is_board_full() or depth == 0:
            return (None, self.score_position(self.ai))
        
        valid_locations = self.get_valid_locations()
        if not valid_locations:
            return (None, 0)
            
        if is_maximizing:
            value = float('-inf')
            column = random.choice(valid_locations)
            for col in valid_locations:
                row = self.drop_piece(col, self.ai)
                if row != -1:  # Valid move
                    new_score = self.alpha_beta(depth - 1, alpha, beta, False)[1]
                    self.remove_piece(row, col)
                    if new_score > value:
                        value = new_score
                        column = col
                    alpha = max(alpha, value)
                    if alpha >= beta:
                        break
            return column, value
        else:
            value = float('inf')
            column = random.choice(valid_locations)
            for col in valid_locations:
                row = self.drop_piece(col, self.player)
                if row != -1:  # Valid move
                    new_score = self.alpha_beta(depth - 1, alpha, beta, True)[1]
                    self.remove_piece(row, col)
                    if new_score < value:
                        value = new_score
                        column = col
                    beta = min(beta, value)
                    if alpha >= beta:
                        break
            return column, value
    
    def get_best_move(self, difficulty='hard'):
        """Returns the best move for the AI"""
        valid_locations = self.get_valid_locations()
        if not valid_locations:
            return -1
        
        if difficulty == 'easy' and random.random() < 0.5:
            return random.choice(valid_locations)
        
        if difficulty == 'easy':
            depth = 2
        else:  # hard
            depth = 4
        
        if difficulty == 'hard':
            column, _ = self.alpha_beta(depth, float('-inf'), float('inf'), True)
        else:
            column, _ = self.minimax(depth, True)
            
        return column

def handle_request(data):
    """Handle API request"""
    try:
        game = ConnectFour()
        
        if 'board' in data:
            board_data = data['board']
            if isinstance(board_data, list) and len(board_data) == game.rows and len(board_data[0]) == game.columns:
                game.board = board_data
        
        difficulty = data.get('difficulty', 'hard')
        
        if 'make_move' in data:
            col = data['make_move']
            if 0 <= col < game.columns and game.is_valid_location(col):
                row = game.drop_piece(col, game.player)
                
                if game.check_win(game.player):
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'player'
                    }
                elif game.is_board_full():
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'draw'
                    }
                
                ai_col = game.get_best_move(difficulty)
                if ai_col >= 0 and game.is_valid_location(ai_col):
                    ai_row = game.drop_piece(ai_col, game.ai)
                    
                    if game.check_win(game.ai):
                        return {
                            'board': game.board,
                            'game_over': True,
                            'winner': 'ai',
                            'ai_move': int(ai_col)
                        }
                    elif game.is_board_full():
                        return {
                            'board': game.board,
                            'game_over': True,
                            'winner': 'draw',
                            'ai_move': int(ai_col)
                        }
                    else:
                        return {
                            'board': game.board,
                            'game_over': False,
                            'ai_move': int(ai_col)
                        }
        
        if 'get_ai_move' in data:
            ai_col = game.get_best_move(difficulty)
            if ai_col >= 0 and game.is_valid_location(ai_col):
                ai_row = game.drop_piece(ai_col, game.ai)
                
                if game.check_win(game.ai):
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'ai',
                        'ai_move': int(ai_col)
                    }
                elif game.is_board_full():
                    return {
                        'board': game.board,
                        'game_over': True,
                        'winner': 'draw',
                        'ai_move': int(ai_col)
                    }
                else:
                    return {
                        'board': game.board,
                        'game_over': False,
                        'ai_move': int(ai_col)
                    }
        
        return {
            'board': game.board,
            'game_over': game.is_game_over()
        }
    
    except Exception as e:
        return {
            'error': str(e)
        }

if __name__ == "__main__":
    try:
        data = json.loads(sys.stdin.read())
        result = handle_request(data)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'error': str(e)})) 